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

    /**
     * @return array token
     */
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

    /**
     * setup defaults
     */
    public function setUp()
    {
        self::bootKernel();
        $this->container          = self::$kernel->getContainer();
        $mailHelper               = $this->container->get('mautic.helper.mailer');
        $token                    = $this->getToken();
        $this->passwordResetModel = new PasswordResetModel($token, $mailHelper);
    }

    /**
     * test password secret string
     */
    public function testSecret()
    {
        $token = $this->getToken()['secret'];
        $this->assertEquals($this->passwordResetModel->getSecret(), $token);
    }

    /**
     * test the password validity
     */
    public function testValidity()
    {
        $validity = $this->getToken()['validity'];
        $this->assertEquals($validity, $this->passwordResetModel->getValidity());
    }

    /**
     * test min length
     */
    public function testMinimumLength()
    {
        $length = $this->getToken()['grg_password_config']['min_length'];
        $this->assertEquals($length, $this->passwordResetModel->getMinLength());
    }

    /**
     * test if number is required in password
     */
    public function testRequireNumber()
    {
        $require = $this->getToken()['grg_password_config']['require_number'];
        $this->assertEquals($require, $this->passwordResetModel->getRequireNumber());
    }

    /**
     * test if cap is required in password
     */
    public function testRequireCaps()
    {
        $require = $this->getToken()['grg_password_config']['require_cap'];
        $this->assertEquals($require, $this->passwordResetModel->getRequireCaps());
    }

    /**
     * test if special char is required in password
     */
    public function testRequireSpecial()
    {
        $require = $this->getToken()['grg_password_config']['require_special'];
        $this->assertEquals($require, $this->passwordResetModel->getRequireSpecial());
    }

    /**
     * test wrong password format
     */
    public function testIsValidPasswordFormat()
    {
        $this->assertFalse($this->passwordResetModel->isValidPasswordFormat("wrong"));
    }

    /**
     * test password format
     */
    public function testIsValidPasswordFormatTrue()
    {
        $this->assertTrue($this->passwordResetModel->isValidPasswordFormat("@Rightformat3"));
    }

    /**
     * test if pasword should be checked when present in post
     */
    public function testShouldCheckPasswordTrue()
    {
        $this->assertTrue($this->passwordResetModel->shouldCheckPassword("true"));
    }

    /**
     * test if password should be checked when not included in the post
     */
    public function testShouldCheckPasswordFalse()
    {
        $this->assertFalse($this->passwordResetModel->shouldCheckPassword(null));
    }
}
