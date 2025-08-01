<?php
namespace App\Services\AWS\EC2Service;
use App\Models\EC2Product;
use Illuminate\Http\Request;

class EC2Service {
    protected $ec2Client = null;
    public function __construct(){
        $this->ec2 = new Ec2Client([
            'region'  => config('aws.region'),
            'version' => 'latest',
            'credentials' => [
                'key'    => config('aws.key'),
                'secret' => config('aws.secret'),
            ]
        ]);
    }

    /**
     * action that spawns an ec2 instance atomically
     */
    public function new(
        array $params = []
    ){
        try {
            $result = $this->ec2->runInstances([
                'ImageId'        => $params['image_id'] ?? 'ami-1234567890abcdef0',
                'InstanceType'   => $params['instance_type'] ?? 't2.micro',
                'MinCount'       => 1,
                'MaxCount'       => 1,
                'TagSpecifications' => [
                    [
                        'ResourceType' => 'instance',
                        'Tags' => [
                            ['Key' => 'Name', 'Value' => $params['name'] ?? 'MyInstance'],
                        ],
                    ],
                ],
            ]);
    
            return $result['Instances'][0]['InstanceId'];
        }
        catch(Ec2Exception $e){
            echo "There was a problem creating the VPC: {$e->getAwsErrorMessage()}\n";
            throw $e;
        }
    }

        /**
     * action that spawns an ec2 instance atomically
     */
    public function terminate(
        string $instanceId
    ){
        try {
            $this->ec2->terminateInstances(['InstanceIds' => [$instanceId]]);
        }
        catch(Ec2Exception $e){
            echo "There was a problem creating the VPC: {$e->getAwsErrorMessage()}\n";
            throw $e;
        }
    }

}

// class MockEC2Spawner extends EC2Spawner{}