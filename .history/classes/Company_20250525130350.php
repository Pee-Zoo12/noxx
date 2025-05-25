<?php

class Company
{
    private $companyID;
    private $name;
    private $registrationNumber;
    private $taxID;
    private $address;
    private $phone;
    private $email;
    private $website;
    private $logo;
    private $foundingDate;
    private $businessType;
    private $industry;
    private $settings;
    private $socialMedia;
    private $brandIdentity;
    private $missionStatement;

    // Magic methods for property access
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            $this->save();
        }
    }

    public function __construct($data = null)
    {
        if ($data) {
            $this->companyID = $data['companyID'] ?? generateUniqueId();
            $this->name = $data['name'] ?? '';
            $this->registrationNumber = $data['registrationNumber'] ?? '';
            $this->taxID = $data['taxID'] ?? '';
            $this->address = $data['address'] ?? [];
            $this->phone = $data['phone'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->website = $data['website'] ?? '';
            $this->logo = $data['logo'] ?? 'assets/images/company-logo.png';
            $this->foundingDate = $data['foundingDate'] ?? '';
            $this->businessType = $data['businessType'] ?? '';
            $this->industry = $data['industry'] ?? '';
            $this->settings = $data['settings'] ?? [
                'currency' => 'PHP',
                'timezone' => 'Asia/Manila',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i:s',
                'taxRate' => 12,
                'shippingEnabled' => true,
                'maintenanceMode' => false
            ];
            $this->socialMedia = $data['socialMedia'] ?? [];
            $this->brandIdentity = $data['brandIdentity'] ?? '';
            $this->missionStatement = $data['missionStatement'] ?? '';
        }
    }

    public function save()
    {
        $companies = readJsonFile(COMPANY_FILE);
        if (!isset($companies['companies'])) {
            $companies['companies'] = [];
        }

        $companies['companies'][$this->companyID] = $this->toArray();
        return writeJsonFile(COMPANY_FILE, $companies);
    }

    public function updateSettings($settings)
    {
        $this->settings = array_merge($this->settings, $settings);
        return $this->save();
    }

    public function addSocialMedia($platform, $url)
    {
        $this->socialMedia[$platform] = $url;
        return $this->save();
    }

    public function updateAddress($street, $city, $state, $country, $postalCode)
    {
        $this->address = [
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'country' => $country,
            'postalCode' => $postalCode
        ];
        return $this->save();
    }

    public function toArray()
    {
        return [
            'companyID' => $this->companyID,
            'name' => $this->name,
            'registrationNumber' => $this->registrationNumber,
            'taxID' => $this->taxID,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'logo' => $this->logo,
            'foundingDate' => $this->foundingDate,
            'businessType' => $this->businessType,
            'industry' => $this->industry,
            'settings' => $this->settings,
            'socialMedia' => $this->socialMedia,
            'brandIdentity' => $this->brandIdentity,
            'missionStatement' => $this->missionStatement
        ];
    }

    // Getters and setters
    public function getCompanyID()
    {
        return $this->companyID;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this->save();
    }

    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber($registrationNumber)
    {
        $this->registrationNumber = $registrationNumber;
        return $this->save();
    }

    public function getTaxID()
    {
        return $this->taxID;
    }

    public function setTaxID($taxID)
    {
        $this->taxID = $taxID;
        return $this->save();
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this->save();
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this->save();
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;
        return $this->save();
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this->save();
    }

    public function getFoundingDate()
    {
        return $this->foundingDate;
    }

    public function setFoundingDate($foundingDate)
    {
        $this->foundingDate = $foundingDate;
        return $this->save();
    }

    public function getBusinessType()
    {
        return $this->businessType;
    }

    public function setBusinessType($businessType)
    {
        $this->businessType = $businessType;
        return $this->save();
    }

    public function getIndustry()
    {
        return $this->industry;
    }

    public function setIndustry($industry)
    {
        $this->industry = $industry;
        return $this->save();
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getSocialMedia()
    {
        return $this->socialMedia;
    }

    public function getBrandIdentity()
    {
        return $this->brandIdentity;
    }

    public function setBrandIdentity($brandIdentity)
    {
        $this->brandIdentity = $brandIdentity;
        return $this->save();
    }

    public function getMissionStatement()
    {
        return $this->missionStatement;
    }

    public function setMissionStatement($missionStatement)
    {
        $this->missionStatement = $missionStatement;
        return $this->save();
    }

    // Static methods
    public static function getInstance()
    {
        $companyData = readJsonFile(COMPANY_FILE);

        if (empty($companyData['companies'])) {
            // Create default company data if none exists
            $defaultCompany = [
                'name' => 'NOX Clothing',
                'brandIdentity' => 'Modern and trendy fashion for everyone',
                'missionStatement' => 'To provide high-quality, affordable clothing that makes people feel confident and stylish',
                'logo' => 'assets/images/noxlogo.png',
                'settings' => [
                    'currency' => 'PHP',
                    'timezone' => 'Asia/Manila',
                    'dateFormat' => 'Y-m-d',
                    'timeFormat' => 'H:i:s',
                    'taxRate' => 12,
                    'shippingEnabled' => true,
                    'maintenanceMode' => false
                ]
            ];

            // Save default company data
            writeJsonFile(COMPANY_FILE, ['companies' => [$defaultCompany]]);
            return new Company($defaultCompany);
        }

        // Return new Company instance with the first company's data
        return new Company(reset($companyData['companies']));
    }

    public static function getSetting($key)
    {
        $company = self::getInstance();
        return $company->settings[$key] ?? null;
    }

    public static function updateSetting($key, $value)
    {
        $company = self::getInstance();
        $company->settings[$key] = $value;
        return $company->save();
    }
}
