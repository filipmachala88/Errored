<?php
declare(strict_types=1);

namespace App\Model;

use Exception;
use Nette\Security\Authenticator as A;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;


class Authenticator implements A
{
    public function __construct(
        private UserManager $userManager,
        private Passwords $passwords,
    ) { }

    /**
     * @inheritDoc
     * @throws Exception
     */
    function authenticate(string $user, string $password): IIdentity
    {
        $row = $this->userManager
                        ->getByEmail($user);

        if (!$row) {
            throw new Exception('User not found.');
        }

        if (!$this->passwords->verify($password, $row->password)) {
            throw new Exception('Invalid password.');
        }

        $user = $row->toArray();
        unset($user['password']);

        return new SimpleIdentity(
            $row->id,
            $this->roleManager->findByUserIdToSelect($row->$id), // nebo pole více rolí
            $user
        );
    }
}