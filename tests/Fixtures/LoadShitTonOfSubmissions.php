<?php

namespace App\Tests\Fixtures;

use App\Entity\Forum;
use App\Entity\Submission;
use App\Entity\User;
use App\Entity\UserFlags;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadShitTonOfSubmissions extends AbstractFixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $i = 0;

        foreach ($this->provideSubmissions() as $data) {
            /** @var Forum $forum */
            $forum = $this->getReference('forum-'.$data['forum']);

            /** @var User $user */
            $user = $this->getReference('user-'.$data['user']);

            $submission = new Submission(
                $data['title'],
                $data['url'],
                $data['body'],
                $forum,
                $user,
                $data['ip'],
                false,
                UserFlags::FLAG_NONE,
                $data['timestamp']
            );

            $this->addReference('submission-'.++$i, $submission);

            $manager->persist($submission);
        }

        $manager->flush();
    }

    private function provideSubmissions() {

        for ($i = 0; $i <= 30; ++$i) {
          yield [
            'url' => 'http://www.example.com/some/thing'.$i,
            'title' => 'A submission with a URL and body',
            'body' => 'This is a body.'. $i,
            'ip' => '10.0.13.12',
            'timestamp' => new \DateTime('2017-03-03 03:03'),
            'user' => 'commie',
            'forum' => 'news',
          ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [LoadExampleForums::class];
    }
}
