<?php

use Cocorico\ConfigBundle\DependencyInjection\Compiler\ContainerBuilder;
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
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new JMS\TranslationBundle\JMSTranslationBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Oneup\UploaderBundle\OneupUploaderBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Lexik\Bundle\CurrencyBundle\LexikCurrencyBundle(),
            new Bazinga\Bundle\GeocoderBundle\BazingaGeocoderBundle(),
            new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new Sonata\CoreBundle\SonataCoreBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
            new Sonata\UserBundle\SonataUserBundle('FOSUserBundle'),
            new FOS\MessageBundle\FOSMessageBundle(),
            new WhiteOctober\BreadcrumbsBundle\WhiteOctoberBreadcrumbsBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(),
            new FM\ElfinderBundle\FMElfinderBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
            new Cocorico\CoreBundle\CocoricoCoreBundle(),
            new Cocorico\GeoBundle\CocoricoGeoBundle(),
            new Cocorico\UserBundle\CocoricoUserBundle(),
            new Cocorico\PageBundle\CocoricoPageBundle(),
            new Cocorico\CMSBundle\CocoricoCMSBundle(),
            new Cocorico\BreadcrumbBundle\CocoricoBreadcrumbBundle(),
            new Cocorico\SonataAdminBundle\CocoricoSonataAdminBundle(),
            new Cocorico\MessageBundle\CocoricoMessageBundle(),
            new Cocorico\ContactBundle\CocoricoContactBundle(),
            new Cocorico\ReviewBundle\CocoricoReviewBundle(),
            new Cocorico\ConfigBundle\CocoricoConfigBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test', 'staging'), true)) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Hpatoio\DeployBundle\DeployBundle();
        }


        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    protected function getContainerBuilder()
    {
        return new ContainerBuilder(new ParameterBag($this->getKernelParameters()));
    }

}
