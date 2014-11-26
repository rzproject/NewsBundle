<?php

namespace Rz\NewsBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class FixPostSettingsCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('rz:new:fix-settings');
        $this->setDescription('Fixes existing post by adding settings via default provider');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $postManager = $this->getContainer()->get('sonata.news.manager.post');
        $pool = $this->getContainer()->get('rz_news.pool');

        $output->writeln("<fg=yellow;options=bold>Fetching all post</fg=yellow;options=bold>");

        $posts = $postManager->findAll();

        if(count($posts) > 0) {
            foreach($posts as $post) {
                if(!$post->getSettings()) {
                    $output->writeln(sprintf("<fg=yellow;options=bold>-></fg=yellow;options=bold> <fg=green>Processing post: %s (%s)</fg=green>", $post->getSlug(), $post->getId()));

                    if($collection = $post->getCollection()) {
                        if ($pool->hasCollection($collection->getSlug())) {
                            $config = $pool->getTemplateByCollection($collection->getSlug(), 'default');
                        } else {
                            $config = $pool->getTemplateByCollection($pool->getDefaultCollection(), 'default');
                        }

                        $post->setSettings(array('template'=>$config['path']));
                        $postManager->save($post);

                    }

                } else {
                    $output->writeln(sprintf("<fg=yellow;options=bold>-></fg=yellow;options=bold> <fg=red>Igoring post: %s (%s) settings already exist</fg=red>", $post->getSlug(), $post->getId()));
                }
            }
        } else {
            $output->writeln("<fg=red;options=bold>No Post found!</fg=red;options=bold>");
        }

        $output->writeln("<fg=yellow;options=bold>Done!</fg=yellow;options=bold>");
    }
}
