<?php
/**
 * Created by PhpStorm.
 * User: odiahv
 * Date: 12/01/2018
 * Time: 12:11.
 */

namespace MauticPlugin\GRGPasswordResetOverrideBundle\Tests\Model;

use MauticPlugin\GRGPasswordResetOverrideBundle\Model\PasswordResetModel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PasswordResetModelTest extends KernelTestCase
{
    /**
     * @var PasswordResetModel
     */
    protected $passwordResetModel;

    protected function getToken()
    {
        return [
            'secret'              => 'qhdohouhyfuewofyoeyfoeyfopwyywruweyporuieyoryeowyruewyropwey',
            'validity'            => 30 * 60,
            'grg_password_config' => [
                'min_length'      => 8,
                'require_cap'     => true,
                'require_special' => true,
                'require_number'  => true,
            ],
        ];
    }

    public function setUp()
    {
        self::bootKernel();
        $this->container          = self::$kernel->getContainer();
        $mailHelper               = $this->container->get('mautic.helper.mailer');
        $token                    = $this->getToken();
        $this->passwordResetModel = new PasswordResetModel($token, $mailHelper);
    }

    public function testSecret()
    {
        $token = $this->getToken()['secret'];
        $this->assertEquals($this->passwordResetModel->getSecret(), $token);
    }

    public function testValidity()
    {
        $validity = $this->getToken()['validity'];
        $this->assertEquals($validity, $this->passwordResetModel->getValidity());
    }

    public function testMinimumLength()
    {
        $length = $this->getToken()['grg_password_config']['min_length'];
        $this->assertEquals($length, $this->passwordResetModel->getMinLength());
    }

    public function testRequireNumber()
    {
        $require = $this->getToken()['grg_password_config']['require_number'];
        $this->assertEquals($require, $this->passwordResetModel->getRequireNumber());
    }

    public function testRequireCaps()
    {
        $require = $this->getToken()['grg_password_config']['require_cap'];
        $this->assertEquals($require, $this->passwordResetModel->getRequireCaps());
    }

    
    public function testRequireSpecial()
    {
        $require = $this->getToken()['grg_password_config']['require_special'];
        $this->assertEquals($require, $this->passwordResetModel->getRequireSpecial());
    }
    
    
    public function testIsValidPasswordFormat()
    {
        $this->assertFalse($this->passwordResetModel->isValidPasswordFormat("wrong"));
    }
    public function testIsValidPasswordFormatTrue()
    {
        $this->assertTrue($this->passwordResetModel->isValidPasswordFormat("@Rightformat3"));
    }
    
    public function testShouldCheckPasswordTrue()
    {
        $this->assertTrue($this->passwordResetModel->shouldCheckPassword("true"));
    }
    
    public function testShouldCheckPasswordFalse()
    {
        $this->assertFalse($this->passwordResetModel->shouldCheckPassword(null));
    }
}
