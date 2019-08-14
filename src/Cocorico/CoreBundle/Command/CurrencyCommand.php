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

use Lexik\Bundle\CurrencyBundle\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

//Cron: 30 17  * * *  user   php bin/console cocorico:currency:update

class CurrencyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cocorico:currency:update')
            ->setDescription('Update DB currencies rates and generate JSON file. To execute daily around 5PM.')
            ->setHelp("Usage php bin/console cocorico:currency:update");
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('lexik:currency:import');

        $arguments = array(
            'command' => 'lexik:currency:import',
            'adapter' => 'ecb'
        );

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        //Generate JSON

        $em = $this->getContainer()->get('doctrine')->getManager();
        $currencies = $em->getRepository(
            $this->getContainer()->getParameter('lexik_currency.currency_class')
        )->findAll();

        $result = array();
        /** @var Currency $currency */
        foreach ($currencies as $currency) {
            $result[$currency->getCode()] = $currency->getRate();
        }

        if (count($result)) {
            $result = json_encode($result);
            //Currencies json file
            $file = $this->getContainer()->getParameter('cocorico.currencies_json');
            $fs = $this->getContainer()->get('filesystem');
            try {
                if (!$fs->exists(dirname($file))) {
                    $fs->mkdir(dirname($file));
                }

                $fs->dumpFile($file, $result);

                $output->writeln("Currencies updated");

                return true;
            } catch (IOException $e) {
                throw new IOException("An error occurred while creating " . $file);
            }
        }

        return false;
    }
}
