<?php
require_once(__DIR__ . '/vendor/google/apiclient/autoload.php');

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

    public function getWhoId($instance, $name) {
        foreach ($instance as $item) {
            if($item->getName() === $name) {
                return $accountId = $item->getId();
                
            }
        }

        return null;
    }

    public function getAccountId($name) {
        $accounts = $this->listAccounts()->getItems();
        $accountId = $this->getWhoId($accounts, $name);

        return $accountId;
    }

    public function getWebpropertieId($accountId, $name) {
        $webproperties = $this->analytics->management_webproperties->listManagementWebproperties($accountId);
        $webpropertyId = $this->getWhoId($webproperties, $name);

        return $webpropertyId;
    }

    public function getProfileId($accountId, $webpropertyId, $index = 0) {
        $profiles = $this->analytics->management_profiles->listManagementProfiles($accountId, $webpropertyId);

        return $profiles[$index]->getId();

    }

    public function setAccountId($name) {
        $this->accountId = $this->getAccountId($name);
        return $this;
    }

    public function setWebpropertieId($name) {
        $this->webpropertyId = $this->getWebpropertieId($this->accountId, $name);
        return $this;
    }

    public function setProfileId($index = 0) {
        $this->profileId = $this->getProfileId($this->accountId, $this->webpropertyId, $index);
        return $this;
    }

    public function getResults($start_date, $end_date, $metrics, $optParams = []) {
        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,
            $start_date,
            $end_date,
            $metrics,
            $optParams
        );
    }
}

// $applicationName = 'newcongress';
// $account = '433729975312-ar2hp401av11nsgadevcqk8fhuodn8uh@developer.gserviceaccount.com';
// $key = 'newcongress-tw-be61ca6250aa.p12';

// $analytics = new Google_Analytics($applicationName, $account, $key);

// $analytics->setAccountId('逐風者')->setWebpropertieId('逐風者')->setProfileId();

// $data = $analytics->getResults(
//             '2015-03-22', 
//             '2015-04-24', 
//             'ga:pageviews', [
//                 'dimensions'=>'ga:pagePath,ga:date', 
//                 'sort'=>'-ga:pageviews'
//             ]
//         );

// foreach($data->getRows() as $row){
//     echo $row[0] . "\t" . $row[1] . "\t" . $row[2] . "\n";
// }

