<?php

namespace App\Http\Controllers;

use App\Models\EC2Product;
use Illuminate\Http\Request;

class EC2Spawner {
    private $ec2Client = null;
    public function __construct(){
        // set up $ec2Client from envs
    }

    /**
     * action that spawns an ec2 instance atomically
     */
    public function spawn(
    ){
        try {
            $result = $this->ec2Client->createVpc([
                "CidrBlock" => $cidr,
            ]);
            return $result['Vpc'];
        }
        catch(Ec2Exception $e){
            echo "There was a problem creating the VPC: {$e->getAwsErrorMessage()}\n";
            throw $e;
        }
    }

}

// class MockEC2Spawner extends EC2Spawner{}