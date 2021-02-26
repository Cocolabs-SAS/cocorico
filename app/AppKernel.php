<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            # new Symfony\Bundle\AsseticBundle\AsseticBundle(),
	    new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
	    new Http\HttplugBundle\HttplugBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\UserBundle\SonataUserBundle(),
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new Oneup\UploaderBundle\OneupUploaderBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Lexik\Bundle\CurrencyBundle\LexikCurrencyBundle(),
            new Bazinga\GeocoderBundle\BazingaGeocoderBundle(),
            new FOS\MessageBundle\FOSMessageBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new WhiteOctober\BreadcrumbsBundle\WhiteOctoberBreadcrumbsBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new FOS\CKEditorBundle\FOSCKEditorBundle(),
            new FM\ElfinderBundle\FMElfinderBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
            new Sentry\SentryBundle\SentryBundle(),
            //Required bundles
            new Cocorico\CoreBundle\CocoricoCoreBundle(),
            new Cocorico\GeoBundle\CocoricoGeoBundle(),
            new Cocorico\UserBundle\CocoricoUserBundle(),
            new Cocorico\PageBundle\CocoricoPageBundle(),
            new Cocorico\CMSBundle\CocoricoCMSBundle(),
            new Cocorico\BreadcrumbBundle\CocoricoBreadcrumbBundle(),
            new Cocorico\SonataAdminBundle\CocoricoSonataAdminBundle(),
            new Cocorico\SonataUserBundle\CocoricoSonataUserBundle(),
            new Cocorico\MessageBundle\CocoricoMessageBundle(),
            new Cocorico\ContactBundle\CocoricoContactBundle(),
            new Cocorico\ReviewBundle\CocoricoReviewBundle(),
            //new Cocorico\ConfigBundle\CocoricoConfigBundle(),
            new Cocorico\TimeBundle\CocoricoTimeBundle(),
            new Cocorico\TrackerBundle\CocoricoTrackerBundle(),

        );

        if (in_array($this->getEnvironment(), array('dev', 'test', 'staging'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Hpatoio\DeployBundle\DeployBundle();
            $bundles[] = new \Symfony\Bundle\MakerBundle\MakerBundle();

            if ('dev' === $this->getEnvironment()) {
                # $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            }
        }


        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    /** @inheritdoc */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');

    }

}
