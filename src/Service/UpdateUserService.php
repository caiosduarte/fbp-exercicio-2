<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Error\AppError;


class UpdateUserService {
    private EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function execute($userFromRequest): User 
    {
        
        $validateUserService = new ValidateUserService($this->manager);
        $validatedUser = $validateUserService->execute($userFromRequest);

        $getUserService = new GetUserService($this->manager);
        $user = $getUserService->execute($validatedUser->getId());

        $user->setName($validatedUser->getName());
        $user->setEmail($validatedUser->getEmail());
        $user->clearTelephones();

        foreach($validatedUser->getTelephones() as $telephone) 
        {
            $user->addTelephone($telephone->getNumber());
        }          

        try 
        {            
            $this->manager->beginTransaction();                 
            $this->manager->persist($user);          
            $this->manager->flush();
            $this->manager->commit();
        }
        catch(\Exception $ex) 
        {
            $this->manager->rollback();
            throw new AppError("Error Processing Request: " . $ex->getMessage(), 500);            
        }

        return $user;  

    }
}