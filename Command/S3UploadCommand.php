<?

namespace Liip\ImagineBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class S3UploadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('liip:s3upload')
            ->setDescription('Cron script to upload queued images to s3')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	// Get the full path where cached images are stored
    	$cachePath = $this->getContainer()->getParameter('liip_imagine.web_root') . $this->getContainer()->getParameter('liip_imagine.cache_prefix') . '/';
    	
    	$em = $this->getContainer()->get('doctrine')->getEntityManager();

    	$repository = $this->getContainer()->get('doctrine')->getRepository('LiipImagineBundle:LiipS3Image');

    	// Get a list of queued images that have not been uploaded to s3
    	$result = $repository->findBy(array('url' => NULL));

    	foreach( $result as $s3Image )
		{
			try
			{
				$filename = $s3Image->getFilename();

				$fs = $this->getContainer()->get('liip_imagine.s3.fs');

				$fs->write($filename, $s3Image->getData(), FALSE, array(
					'content-type' => $s3Image->getMimetype()
				));

				// Set the newly uploaded file's ACL to public
				$s3 = $this->getContainer()->get('liip_imagine.s3');
				$s3->set_object_acl($this->getContainer()->getParameter('liip_imagine.s3.bucket_name'), $filename, \AmazonS3::ACL_PUBLIC);

				// Set the image's live url, // prefix for http/https
				$s3Image->setUrl('//' . 's3.amazonaws.com/' . $this->getContainer()->getParameter('liip_imagine.s3.bucket_name') . '/' . $filename);
				
				// Set the data to blank to preserve disk space
				$s3Image->setData(NULL);
				$em->persist($s3Image);
				
				unlink($cachePath . $filename);
			}
			catch( \Exception $e )
			{
				$logger = $this->getContainer()->get('logger');
				$logger->err($e);
			}
    	}

		$em->flush();

        $output->writeln(sprintf('Completed - uploaded %d images.', count($result)));
    }

}
