<?php

namespace App\DataFixtures;

use App\Entity\Property;
use App\Entity\PropertyFeature;
use App\Entity\PropertyImage;
use App\Entity\PropertyPdf;
use App\Entity\Contact;
use App\Entity\User;
use App\Entity\Blog;
use App\Entity\PropertyInquiry;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class PropertyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('bg_BG');
        $faker->addProvider(new Provider\BulgarianProvider($faker));

        // Create property statuses
        $statuses = ['available', 'sold', 'reserved'];
        
        // Create more realistic price ranges
        $priceRanges = [
            'industrial_land' => ['min' => 30, 'max' => 120], // цена на кв.м
            'industrial_building' => ['min' => 400, 'max' => 800],
            'logistics_center' => ['min' => 500, 'max' => 1000],
            'warehouse' => ['min' => 300, 'max' => 600],
            'production_facility' => ['min' => 450, 'max' => 900]
        ];

        // Create admin user
        $admin = new User();
        $admin->setEmail('admin@example.com')
            ->setName('Admin User')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('$2y$13$...')  // Use your password hasher service
            ->setIsActive(true);
        $manager->persist($admin);

        // Create properties
        $properties = [];
        $propertyTypes = ['industrial_land', 'industrial_building', 'logistics_center', 'warehouse', 'production_facility'];
        
        for ($i = 0; $i < 10; $i++) {
            $type = $faker->randomElement($propertyTypes);
            $area = $faker->numberBetween(1000, 50000);
            $pricePerSqm = $faker->numberBetween(
                $priceRanges[$type]['min'],
                $priceRanges[$type]['max']
            );

            $property = new Property();
            $property
                ->setTitleBg(sprintf('%s в %s', 
                    $faker->industrialArea(), 
                    $faker->bulgarianCity()
                ))
                ->setTitleEn($faker->sentence())
                ->setTitleDe($faker->sentence())
                ->setTitleRu($faker->sentence())
                ->setDescriptionBg($faker->paragraphs(3, true))
                ->setDescriptionEn($faker->paragraphs(3, true))
                ->setDescriptionDe($faker->paragraphs(3, true))
                ->setDescriptionRu($faker->paragraphs(3, true))
                ->setLocationBg($faker->city())
                ->setLocationEn($faker->city())
                ->setLocationDe($faker->city())
                ->setLocationRu($faker->city())
                ->setArea($area)
                ->setPrice($area * $pricePerSqm)
                ->setType($type)
                ->setIsActive(true)
                ->setIsFeatured($faker->boolean(20))
                ->setLatitude($faker->latitude(41, 44))
                ->setLongitude($faker->longitude(22, 28))
                ->setSlug($faker->slug())
                ->setStatus($faker->randomElement($statuses))
                ->setPricePerSqm($pricePerSqm)
                ->setYearBuilt($faker->numberBetween(1990, 2023))
                ->setAvailableFrom(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+6 months')));

            // Add features
            for ($j = 0; $j < 5; $j++) {
                $feature = new PropertyFeature();
                $feature->setFeatureBg($faker->word())
                    ->setFeatureEn($faker->word())
                    ->setFeatureDe($faker->word())
                    ->setFeatureRu($faker->word())
                    ->setProperty($property);
                $manager->persist($feature);
            }

            // Add images
            for ($k = 0; $k < 3; $k++) {
                $image = new PropertyImage();
                $image->setFilename($faker->uuid() . '.jpg')
                    ->setIsMain($k === 0)
                    ->setPosition($k)
                    ->setProperty($property);
                $manager->persist($image);
            }

            // Add PDF files
            for ($l = 0; $l < 2; $l++) {
                $pdf = new PropertyPdf();
                $pdf->setFilename($faker->uuid() . '.pdf')
                    ->setTitle('Брошура ' . ($l + 1))
                    ->setTitleEn('Brochure ' . ($l + 1))
                    ->setProperty($property);
                $manager->persist($pdf);
            }

            $properties[] = $property;
            $manager->persist($property);
        }

        // Create blog posts
        for ($i = 0; $i < 5; $i++) {
            $blog = new Blog();
            $blog->setTitle($faker->sentence())
                ->setContent($faker->paragraphs(5, true))
                ->setSlug($faker->slug())
                ->setLanguage($faker->randomElement(['bg', 'en']))
                ->setStatus('published')
                ->setViews($faker->numberBetween(0, 1000));
            $manager->persist($blog);
        }

        // Create inquiries
        foreach ($properties as $property) {
            for ($i = 0; $i < 2; $i++) {
                $inquiry = new PropertyInquiry();
                $inquiry->setName($faker->name())
                    ->setEmail($faker->email())
                    ->setPhone($faker->phoneNumber())
                    ->setMessage($faker->paragraph())
                    ->setIsRead(false)
                    ->setProperty($property);
                $manager->persist($inquiry);
            }
        }

        // Create contact messages
        for ($i = 0; $i < 5; $i++) {
            $contact = new Contact();
            $contact->setName($faker->name())
                ->setEmail($faker->email())
                ->setPhone($faker->phoneNumber())
                ->setSubject($faker->sentence())
                ->setMessage($faker->paragraph())
                ->setIpAddress($faker->ipv4())
                ->setStatus('new');
            $manager->persist($contact);
        }

        $manager->flush();
    }
}
