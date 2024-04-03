<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('mask_email', [$this, 'maskEmail']),
            new TwigFilter('mask_phone', [$this, 'maskPhone']),
        ];
    }

    public function maskEmail($email)
    {
        if (empty($email)) {
            return '';
        }
        
        $em = explode("@", $email);
        $name = str_repeat('*', strlen($em[0]) - 1).substr($em[0], -1);
        return $name.'@'.$em[1];
    }

    public function maskPhone($phoneNumber)
    {
        if (empty($phoneNumber)) {
            return '';
        }
        
        return substr($phoneNumber, 0, 6) . str_repeat('*', strlen($phoneNumber) - 6);
    }
}
