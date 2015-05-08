<?php
namespace ApplicationTest\Controller;

use Zend\Cache\StorageFactory;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use PHPUnit_Framework_ExpectationFailedException;

/**
 * Class BaseControllerTestCase
 *
 * @package ApplicationTest\Controller
 */
abstract class BaseControllerTestCase extends AbstractHttpControllerTestCase
{
    protected $cache;

    protected function setUpWithUser($role = 'OPG User', $userId = 'norole.user@opgtest.com')
    {
        $this->setApplicationConfig(
            include getcwd() . '/config/application.config.php'
        );

        parent::setUp();

        $this->getRequest()
            ->getHeaders()
            ->addHeaderLine('X-USER-ID', $userId);

        $this->getRequest()
            ->getHeaders()
            ->addHeaderLine('X-USER-ROLE', $role);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->getApplication()
            ->getServiceManager()
            ->get('Doctrine\ORM\EntityManager')
            ->getConnection()
            ->close();


        unset($this->cache);
    }

    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    protected function getCache()
    {
        if (empty($this->cache)) {

            // Via factory:
            $this->cache = StorageFactory::factory(array(
                'adapter' => array(
                    'name'    => 'filesystem',
                    'options' => array('cache_dir' => getcwd() . '/data/cache/test')
                ),
                'plugins' => array(
                    'exception_handler' => array('throw_exceptions' => false),
                ),
            ));
        }

        return $this->cache;
    }



    /**
     * Assert response status code
     *
     * Overridden to allow us to show a description
     *
     * @param int  $code
     * @param string $description
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertResponseStatusCode($code, $description = null)
    {
        if ($description == null) {
            return parent::assertResponseStatusCode($code);
        }

        if ($this->useConsoleRequest) {
            if (!in_array($code, array(0, 1))) {
                throw new PHPUnit_Framework_ExpectationFailedException(
                    'Console status code assert value must be O (valid) or 1 (error)'
                );
            }
        }
        $match = $this->getResponseStatusCode();

        if ($code != $match) {
            throw new PHPUnit_Framework_ExpectationFailedException(sprintf(
                $description . PHP_EOL .
                'Failed asserting response code "%s", actual status code is "%s"',
                $code,
                $match
            ));
        }
        $this->assertEquals($code, $match);
    }

    public function generateRandomUserDetails()
    {
        return array(
            'firstname'                     => 'FN_' . uniqid(),
            'surname'                       => 'SN_' . uniqid(),
            'phoneNumbers'                  =>
                array(
                    array(
                        'type' => 'Work',
                        'phoneNumber' => uniqid(),
                    ),
                    array(
                        'type' => 'Home',
                        'phoneNumber' => uniqid(),
                    ),
                ),
            'email'                         => uniqid() . '@opgtest.com',
            'dob'                           => mt_rand(10, 28) . '/' . mt_rand(10, 12) .'/' . mt_rand(1950, 1996),
            'addresses'                     =>
            array(
                array(
                    'addressLines' => array( uniqid(),uniqid(),uniqid()),
                    'town' => uniqid(),
                    'postcode' => substr(uniqid(), 0, 6),
                ),
            )

        );
    }

    public function generateRandomUserWithoutAddressOrTelephoneNumber()
    {
        return array(
            'firstname' => 'FN_' . uniqid(),
            'surname'   => 'SN_' . uniqid(),
            'email'     => uniqid() . '@opgtest.com',
            'dob'       => mt_rand(10, 28) . '/' . mt_rand(10, 12) .'/' . mt_rand(1950, 1996)
        );
    }

    public function generateRandomUserWithBlankAddress()
    {
        return array(
            'firstname' => 'FN_' . uniqid(),
            'surname'   => 'SN_' . uniqid(),
            'email'     => uniqid() . '@opgtest.com',
            'dob'       => mt_rand(10, 28) . '/' . mt_rand(10, 12) .'/' . mt_rand(1950, 1996),
            'addresses' =>
            array(
                array(
                    'addressLines' => array('', '', ''),
                    'country'      => '',
                    'county'       => '',
                    'postcode'     => '',
                    'town'         => ''
                ),
            ),
            'addressLines' => array('', '', ''),
            'country'      => '',
            'county'       => '',
            'postcode'     => '',
            'town'         => '',
        );
    }

    public function generateRandomUserWithoutAddress()
    {
        return array(
            'firstname'    => 'FN_' . uniqid(),
            'surname'      => 'SN_' . uniqid(),
            'phoneNumbers' =>
            array(
                array(
                    'type' => 'Work',
                    'phoneNumber' => uniqid(),
                ),
                array(
                    'type' => 'Home',
                    'phoneNumber' => uniqid(),
                ),
            ),
            'email'        => uniqid() . '@opgtest.com',
            'dob'          => mt_rand(10, 28) . '/' . mt_rand(10, 12) .'/' . mt_rand(1950, 1996)
        );
    }

    public function createDonor()
    {
        $personSrc = $this->generateRandomUserDetails();
        $this->dispatch('/api/person', 'POST', $personSrc);
        $dataDonor = json_decode($this->getResponse()->getContent(), true)['data'];
        $this->reset();
        $this->setUp();
        return $dataDonor;
    }

    public function getRandomUserId($excludeEmails = array())
    {

        $this->dispatch('/api/user', 'GET');

        $userListWithoutAssignedUser = array();
        $response = json_decode($this->getResponse()->getContent(), true)['data'];
        foreach ($response as $user) {
            if (!in_array($user['email'], $excludeEmails)) {
                $userListWithoutAssignedUser[] = $user;
            }
        }
        $randomUserId = mt_rand(0, count($userListWithoutAssignedUser) -1);
        $this->reset();
        $this->setUp();

        return $randomUserId;
    }
}
