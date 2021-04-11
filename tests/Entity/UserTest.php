<?php


namespace App\Tests\Entity;

use App\Entity\User;

use PHPUnit\Framework\TestCase;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserTest extends TestCase
{
    private $passwordEncoder;




    public function testUserCreate(){


        $user = new User();
        $user->setEmail('admin@example.org');
//        $user->setPassword($this->passwordEncoder->encodePassword(
//            $user,
//            'password'
//        ));
        $user->setRoles(['ROLE_USER','ROLE_ADMIN']);
//        $manager->persist($user);
        $this->assertEquals("admin@example.org", $user->getEmail());
        $this->assertEquals(['ROLE_USER','ROLE_ADMIN'], $user->getRoles());
//        if(!$this->passwordEncoder->isPasswordValid($user, 'password')) {
//            $this->assertTrue(true);
//        }


    }
}