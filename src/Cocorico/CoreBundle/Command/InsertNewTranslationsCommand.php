<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Copy  current locale DB content translations into a new locale
 */

class InsertNewTranslationsCommand extends ContainerAwareCommand
{

    public function configure()
    {
        $this
            ->setName('cocorico:db:insert-new-translations')
            ->setDescription('Copy DB translations content from existing locale to a new locale')
            ->setHelp("Usage php bin/console cocorico:db:insert-new-translations");
    }

    /** @inheritdoc */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $newLocale = 'en';

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $con = $em->getConnection();

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO listing_translation (translatable_id, title, description, rules, locale, slug)
            SELECT lt.translatable_id, lt.title, lt.description, lt.rules, '{$newLocale}', lt.slug FROM listing_translation lt
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO user_translation (translatable_id, description, locale)
            SELECT ut.translatable_id, ut.description, '{$newLocale}' FROM user_translation ut
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO booking_option_translation (translatable_id, name, description, locale)
            SELECT bot.translatable_id, bot.name, bot.description,'{$newLocale}' FROM booking_option_translation bot
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO geo_area_translation (translatable_id, name, locale, slug)
            SELECT gat.translatable_id, gat.name, '{$newLocale}', gat.slug FROM geo_area_translation gat
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO geo_city_translation (translatable_id, name, locale, slug)
            SELECT gct.translatable_id, gct.name, '{$newLocale}', gct.slug FROM geo_city_translation gct
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO geo_country_translation (translatable_id, name, locale, slug)
            SELECT gct.translatable_id, gct.name, '{$newLocale}', gct.slug FROM geo_country_translation gct
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO geo_department_translation (translatable_id, name, locale, slug)
            SELECT gdt.translatable_id, gdt.name, '{$newLocale}', gdt.slug FROM geo_department_translation gdt
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO listing_category_translation (translatable_id, name, locale, slug)
            SELECT lct.translatable_id, lct.name, '{$newLocale}', lct.slug FROM listing_category_translation lct
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO listing_characteristic_group_translation (translatable_id, name, locale)
            SELECT lcgt.translatable_id, lcgt.name, '{$newLocale}' FROM listing_characteristic_group_translation lcgt
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO listing_characteristic_translation (translatable_id, name, description, locale)
            SELECT lchar.translatable_id, lchar.name, lchar.description, '{$newLocale}' FROM listing_characteristic_translation lchar
SQLQUERY;
        $con->executeQuery($sql);


        $sql = <<<SQLQUERY
            INSERT IGNORE INTO listing_characteristic_value_translation (translatable_id, name, locale)
            SELECT lcharv.translatable_id, lcharv.name, '{$newLocale}' FROM listing_characteristic_value_translation lcharv
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO page_translation (translatable_id, meta_title, meta_description, title, description, locale, slug)
            SELECT pt.translatable_id, pt.meta_title, pt.meta_description, pt.title, pt.description, '{$newLocale}', pt.slug FROM page_translation pt
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO listing_option_translation (translatable_id, name, description, locale)
            SELECT lot.translatable_id, lot.name, lot.description, '{$newLocale}' FROM listing_option_translation lot
SQLQUERY;
        $con->executeQuery($sql);

        $sql = <<<SQLQUERY
            INSERT IGNORE INTO footer_translation (translatable_id, url, url_hash, title, link, locale)
            SELECT fr.translatable_id, fr.url, fr.url_hash, fr.title, fr.link, '{$newLocale}' FROM footer_translation fr
SQLQUERY;
        $con->executeQuery($sql);

        $output->writeln("New translations added!");
    }

}
