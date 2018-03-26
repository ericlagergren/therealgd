<?php

namespace App\Fixtures;

use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;

class LoadShitTonOfUsers extends AbstractFixture implements ORMFixtureInterface {

	private $container = null;

	private function _generateRandomString($length = 10) {
		$characters = '999Do_IRLBOIdrkSLCkikkekLOLlolYEAgrlshtSHTSUMdigdikget666777MFKreeREErEegetsumlikmaabut';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= substr($characters, 3*rand(0, $charactersLength/3 - 1), 3);
		}
		return $randomString;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load(ObjectManager $manager) {
		$i = 1;
		foreach ($this->provideUsers() as $data) {
          $manager->getConnection()->exec('BEGIN TRANSACTION;');
          $user = new User($data['username'], $data['password']);
          $user->setAdmin($data['admin']);
          $user->setEmail($data['email']);

          $this->addReference('user-'.$data['username'], $user);

          $manager->persist($user);
          ++$i;
          $manager->getConnection()->exec('COMMIT;');
          $manager->flush();
		}
	}

	private function provideUsers() {
		for ($i = 0; $i < 400; ++$i) {
			$userLength = $i % 7;
			$username = $this->_generateRandomString($userLength).$i;
            // $username = uniqid(); // for parallel OR --append
			yield [
				'username' => $username,
				'password' => $this->_generateRandomString(7), 
				'email' => $username.'@communismwasnevertried.com',
				'admin' => false,
			];
		}

        // Kill these lines for parallel OR --append
		yield [
			'username' => 'commie',
			'password' => 'goodshit',
			'email' => 'commie@example.com',
			'admin' => true,
		];

		yield [
			'username' => 'zach',
			'password' => 'example2',
			'email' => 'zach@example.com',
			'admin' => false,
		];

		yield [
			'username' => 'third',
			'password' => 'example3',
			'email' => 'third@example.net',
			'admin' => false,
		];
	}
}
