<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Binary\FileBinaryInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

abstract class AbstractPostProcessor implements PostProcessorInterface
{
    /**
     * @var string
     */
    protected $executablePath;

    /**
     * @var string|null
     */
    protected $temporaryRootPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(string $executablePath, string $temporaryRootPath = null)
    {
        $this->executablePath = $executablePath;
        $this->temporaryRootPath = $temporaryRootPath;
        $this->filesystem = new Filesystem();
    }

    protected function createProcess(array $arguments = [], array $options = []): Process
    {
        $process = new Process($arguments);

        if (!isset($options['process'])) {
            return $process;
        }

        if (isset($options['process']['timeout'])) {
            $process->setTimeout($options['process']['timeout']);
        }

        if (isset($options['process']['working_directory'])) {
            $process->setWorkingDirectory($options['process']['working_directory']);
        }

        if (isset($options['process']['environment_variables']) && \is_array($options['process']['environment_variables'])) {
            $process->setEnv($options['process']['environment_variables']);
        }

        return $process;
    }

    protected function isBinaryTypeJpgImage(BinaryInterface $binary): bool
    {
        return $this->isBinaryTypeMatch($binary, ['image/jpeg', 'image/jpg']);
    }

    protected function isBinaryTypePngImage(BinaryInterface $binary): bool
    {
        return $this->isBinaryTypeMatch($binary, ['image/png']);
    }

    protected function isBinaryTypeMatch(BinaryInterface $binary, array $types): bool
    {
        return \in_array($binary->getMimeType(), $types, true);
    }

    protected function writeTemporaryFile(BinaryInterface $binary, array $options = [], string $prefix = null): string
    {
        $temporary = $this->acquireTemporaryFilePath($options, $prefix);

        if ($binary instanceof FileBinaryInterface) {
            $this->filesystem->copy($binary->getPath(), $temporary, true);
        } else {
            $this->filesystem->dumpFile($temporary, $binary->getContent());
        }

        return $temporary;
    }

    protected function acquireTemporaryFilePath(array $options, string $prefix = null): string
    {
        $root = $options['temp_dir'] ?? $this->temporaryRootPath ?: sys_get_temp_dir();

        if (!is_dir($root)) {
            try {
                $this->filesystem->mkdir($root);
            } catch (IOException $exception) {
                // ignore failure as "tempnam" function will revert back to system default tmp path as last resort
            }
        }

        if (false === $file = @tempnam($root, $prefix ?: 'post-processor')) {
            throw new \RuntimeException(sprintf('Temporary file cannot be created in "%s"', $root));
        }

        return $file;
    }

    /**
     * @param int[]    $validReturns
     * @param string[] $errors
     */
    protected function isSuccessfulProcess(Process $process, array $validReturns = [0], array $errors = ['ERROR']): bool
    {
        if (\count($validReturns) > 0 && !\in_array($process->getExitCode(), $validReturns, true)) {
            return false;
        }

        foreach ($errors as $string) {
            if (false !== mb_strpos($process->getOutput(), $string)) {
                return false;
            }
        }

        return true;
    }

    protected function triggerSetterMethodDeprecation(string $method): void
    {
        @trigger_error(sprintf('The %s() method was deprecated in 2.2 and will be removed in 3.0. You must '
            .'setup the class state via its __construct() method. You can still pass filter-specific options to the '.
            'process() method to overwrite behavior.', $method), E_USER_DEPRECATED);
    }
}
