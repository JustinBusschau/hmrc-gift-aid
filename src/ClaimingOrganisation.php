<?php

namespace GovTalk\GiftAid;

class ClaimingOrganisation
{
    private $name = '';
    private $hmrcRef = '';
    private $regulator = '';
    private $regNo = '';
    private $hasConnectedCharities = false;
    private $connectedCharities = [];
    private $useCommunityBuildings = false;

    public function __construct(
        $name = null,
        $hmrcRef = null,
        $regulator = null,
        $regNo = null
    ) {
        $this->name = $name;
        $this->hmrcRef = $hmrcRef;
        $this->regulator = $regulator;
        $this->regNo = $regNo;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function getHmrcRef()
    {
        return $this->hmrcRef;
    }

    public function setHmrcRef($value)
    {
        $this->hmrcRef = $value;
    }

    public function getRegulator()
    {
        return $this->regulator;
    }

    public function setRegulator($value)
    {
        $this->regulator = $value;
    }

    public function getRegNo()
    {
        return $this->regNo;
    }

    public function setRegNo($value)
    {
        $this->regNo = $value;
    }

    public function getHasConnectedCharities()
    {
        return $this->hasConnectedCharities;
    }

    public function setHasConnectedCharities($value)
    {
        if (is_bool($value)) {
            $this->hasConnectedCharities = $value;
        } else {
            $this->hasConnectedCharities = false;
        }
    }

    public function getConnectedCharities()
    {
        return $this->connectedCharities;
    }

    public function addConnectedCharity(ClaimingOrganisation $connectedCharity)
    {
        $this->connectedCharities[] = $connectedCharity;
    }

    public function clearConnectedCharities()
    {
        $this->connectedCharities = [];
    }

    public function getUseCommunityBuildings()
    {
        return $this->useCommunityBuildings;
    }

    public function setUseCommunityBuildings($value)
    {
        if (is_bool($value)) {
            $this->useCommunityBuildings = $value;
        } else {
            $this->useCommunityBuildings = false;
        }
    }
}
