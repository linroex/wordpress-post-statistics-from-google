<?php
session_start();

require_once('vendor/google/apiclient/autoload.php');

class Google_Analytics {
    private $accountName;
    private $client;
    private $analytics;
    private $profileId;

    public function __construct($applicationName, $accountName, $keyPath) {
        $this->accountName = $accountName;

        $this->client = new Google_client();
        $this->client->setApplicationName($applicationName);

        $key = file_get_contents($keyPath);
        $credentials = new Google_Auth_AssertionCredentials($this->accountName, ['https://www.googleapis.com/auth/analytics.readonly'], $key);

        $this->client->setAssertionCredentials($credentials);

        if ($this->client->getAuth()->isAccessTokenExpired()) {
          $this->client->getAuth()->refreshTokenWithAssertion($credentials);
        }

        $_SESSION['service_token'] = $this->client->getAccessToken();

        $this->analytics = new Google_Service_Analytics($this->client);
    }

    public function listAccounts() {
        return $this->analytics->management_accounts->listManagementAccounts();
    }

    public function getProfileId($name = '') {
        $accounts = $this->listAccounts()->getItems();

        $accountId;
        foreach ($accounts as $item) {
            if($item->getName() === $name) {
                $accountId = $item->getId();
                break;
            }
        }

        $webproperties = $this->analytics->management_webproperties->listManagementWebproperties($accountId);


    }

    public function setProfileId($name = '') {
        $this->profileId = $this->getProfileId($name);
    }

    public function getResults() {
        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,
            '2015-03-03',
            '2015-04-03',
            'ga:sessions'
        );
    }
}

$applicationName = 'newcongress';
$account = '433729975312-ar2hp401av11nsgadevcqk8fhuodn8uh@developer.gserviceaccount.com';
$key = 'newcongress-tw-be61ca6250aa.p12';

$analytics = new Google_Analytics($applicationName, $account, $key);

// $analytics->setProfileId('New Congress');
// $analytics->setProfileId('逐風者');

// var_dump($analytics->getResults());

var_dump($analytics->getProfileId('New Congress'));