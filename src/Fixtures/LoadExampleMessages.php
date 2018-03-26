<?php

namespace App\Fixtures;

use App\Entity\MessageReply;
use App\Entity\MessageThread;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadExampleMessages extends AbstractFixture implements DependentFixtureInterface, ORMFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        /** @noinspection PhpParamsInspection */
        $thread = new MessageThread(
            $this->getReference('user-zach'),
            'This is a message. There are many like it, but this one originates from a fixture.',
            '192.168.0.3',
            $this->getReference('user-commie'),
            'Example message.'
        );

        /* @noinspection PhpParamsInspection */
        $thread->addReply(new MessageReply(
             $this->getReference('user-commie'),
             'This is a reply to the message originating from a fixture.',
             '192.168.0.4',
             $thread
        ));

        $manager->persist($thread);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [LoadShitTonOfUsers::class];
    }
}
