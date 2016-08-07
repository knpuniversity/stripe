<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WebhookControllerTest extends WebTestCase
{
    private $container;
    /** @var EntityManager */
    private $em;

    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
    }

    private function createSubscription()
    {
        $user = new User();
        $user->setEmail('fluffy'.mt_rand().'@sheep.com');
        $user->setUsername('fluffy'.mt_rand());
        $user->setPlainPassword('baa');

        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->activateSubscription(
            'plan_STRIPE_TEST_ABC'.mt_rand(),
            'sub_STRIPE_TEST_XYZ'.mt_rand(),
            new \DateTime('+1 month')
        );

        $this->em->persist($user);
        $this->em->persist($subscription);
        $this->em->flush();

        return $subscription;
    }
}
