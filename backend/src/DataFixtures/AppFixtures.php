<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Registration;
use App\Entity\OrganizerRequest;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ======================
        // ADMINS
        // ======================
        $admin1 = new User();
        $admin1->setFirstName('Admin1');
        $admin1->setLastName('Test');
        $admin1->setEmail('admin1@test.com');
        $admin1->setRole('ADMIN');
        $admin1->setConsentDate(new \DateTime());
        $admin1->setPassword($this->passwordHasher->hashPassword($admin1, '123456'));
        $manager->persist($admin1);

        $admin2 = new User();
        $admin2->setFirstName('Admin2');
        $admin2->setLastName('Test');
        $admin2->setEmail('admin2@test.com');
        $admin2->setRole('ADMIN');
        $admin2->setConsentDate(new \DateTime());
        $admin2->setPassword($this->passwordHasher->hashPassword($admin2, '123456'));
        $manager->persist($admin2);

        // ======================
        // USERS
        // ======================
        $user1 = new User();
        $user1->setFirstName('User1');
        $user1->setLastName('Test');
        $user1->setEmail('user1@test.com');
        $user1->setRole('USER');
        $user1->setConsentDate(new \DateTime());
        $user1->setPassword($this->passwordHasher->hashPassword($user1, '123456'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setFirstName('User2');
        $user2->setLastName('Test');
        $user2->setEmail('user2@test.com');
        $user2->setRole('USER');
        $user2->setConsentDate(new \DateTime());
        $user2->setPassword($this->passwordHasher->hashPassword($user2, '123456'));
        $manager->persist($user2);

        // ======================
        // ORGANIZERS
        // ======================

        $organizer1 = new User();
        $organizer1->setFirstName('Organizer1');
        $organizer1->setLastName('Test');
        $organizer1->setEmail('organizer1@test.com');
        $organizer1->setRole('ORGANIZER');
        $organizer1->setConsentDate(new \DateTime());
        $organizer1->setPassword($this->passwordHasher->hashPassword($organizer1, '123456'));
        $manager->persist($organizer1);

        $organizer2 = new User();
        $organizer2->setFirstName('Organizer2');
        $organizer2->setLastName('Test');
        $organizer2->setEmail('organizer2@test.com');
        $organizer2->setRole('ORGANIZER');
        $organizer2->setConsentDate(new \DateTime());
        $organizer2->setPassword($this->passwordHasher->hashPassword($organizer2, '123456'));
        $manager->persist($organizer2);


        // ======================
        // EVENTS (créés par admins)
        // ======================
        $event1 = new Event();
        $event1->setTitle('Conférence Tech');
        $event1->setDescription('Une conférence sur les nouvelles technologies');
        $event1->setEventDate(new \DateTime('+2 days'));
        $event1->setLocation('Paris');
        $event1->setMaxParticipants(100);
        $event1->setOrganizer($admin1);
        $manager->persist($event1);

        $event2 = new Event();
        $event2->setTitle('Workshop Symfony');
        $event2->setDescription('Atelier pratique Symfony');
        $event2->setEventDate(new \DateTime('+5 days'));
        $event2->setLocation('Paris');
        $event2->setMaxParticipants(50);
        $event2->setOrganizer($admin1);
        $manager->persist($event2);

        $event3 = new Event();
        $event3->setTitle('Meetup Dev');
        $event3->setDescription('Rencontre entre développeurs');
        $event3->setEventDate(new \DateTime('+10 days'));
        $event3->setLocation('Paris');
        $event3->setMaxParticipants(75);
        $event3->setOrganizer($admin1);
        $manager->persist($event3);

        // ======================
        // REGISTRATIONS
        // ======================

        // user1 inscrit à 2 events
        $r1 = new Registration();
        $r1->setUser($user1);
        $r1->setEvent($event1);
        $r1->setStatus('confirmed');
        $manager->persist($r1);

        $r2 = new Registration();
        $r2->setUser($user1);
        $r2->setEvent($event2);
        $r2->setStatus('confirmed');
        $manager->persist($r2);

        // user2 inscrit à 2 events
        $r3 = new Registration();
        $r3->setUser($user2);
        $r3->setEvent($event2);
        $r3->setStatus('confirmed');
        $manager->persist($r3);

        $r4 = new Registration();
        $r4->setUser($user2);
        $r4->setEvent($event3);
        $r4->setStatus('confirmed');
        $manager->persist($r4);

        $manager->flush();

        $organizerRequest = new OrganizerRequest();
        $organizerRequest->setUser($user1);
        $organizerRequest->setStatus('pending');
        $manager->persist($organizerRequest);
    }
}
