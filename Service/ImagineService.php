<?php


namespace Liip\ImagineBundle\Service;


use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Exception\SignerException;
use Liip\ImagineBundle\ValueObject\ServiceResponse;
use Psr\Log\LoggerInterface;


class ImagineService
{

    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var SignerInterface
     */
    protected $signer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param DataManager $dataManager
     * @param FilterManager $filterManager
     * @param CacheManager $cacheManager
     * @param SignerInterface $signer
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        SignerInterface $signer,
        LoggerInterface $logger = null // TODO: currenty not in use. Need to delete it or find some usage
    )
    {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->signer = $signer;
        $this->logger = $logger;
    }

    public function filter($path, $filter, $resolver)
    {

        if (!$this->cacheManager->isStored($path, $filter, $resolver)) {
            try {
                $binary = $this->dataManager->find($filter, $path);
            } catch (NotLoadableException $e) {
                if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
                    return new ServiceResponse($defaultImageUrl);
                }
                throw $e;
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter),
                $path,
                $filter,
                $resolver
            );
        }

        return new ServiceResponse($this->cacheManager->resolve($path, $filter, $resolver), 301);

    }

    public function filterRuntime(array $filters, $hash, $path, $filter, $resolver)
    {

        if (true !== $this->signer->check($hash, $path, $filters)) {
            throw new SignerException(sprintf(
                'Signed url does not pass the sign check for path "%s" and filter "%s" and runtime config %s',
                $path,
                $filter,
                json_encode($filters)
            ));
        }

        try {
            $binary = $this->dataManager->find($filter, $path);
        } catch (NotLoadableException $e) {
            if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
                return new ServiceResponse($defaultImageUrl);
            }
            throw $e;
        }

        $rcPath = $this->cacheManager->getRuntimePath($path, $filters);

        $this->cacheManager->store(
            $this->filterManager->applyFilter($binary, $filter, [
                'filters' => $filters,
            ]),
            $rcPath,
            $filter,
            $resolver
        );

        return new ServiceResponse($this->cacheManager->resolve($rcPath, $filter, $resolver), 301);
    }


}