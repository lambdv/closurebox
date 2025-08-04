<?php
namespace App\Services;
use App\Models\EC2Product;
use Illuminate\Http\Request;
use Aws\Ec2\Ec2Client;
use Aws\Ec2\Ec2Exception;
use Illuminate\Support\Facades\Log;

// interface VenderVPSService {
//     public function spawn(array $params): array;
//     public function terminate(array $instanceIds): void;
// }

class EC2Service  {
    protected $ec2Client = null;
    
    public function __construct(){
        $this->ec2Client = new Ec2Client([
            'region'  => env('AWS_DEFAULT_REGION'),
            'version' => 'latest',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
    }

    /**
     * action that spawns an ec2 instance atomically
     */
    public function new(
        array $params = []
    ){

        if(empty($params)){
            throw new \Exception('Params are required');
        }

        if(empty($params['name'])){
            throw new \Exception('Name is required');
        }

        try {
            $awsParams = [
                'ImageId' => $params['image_id'] ?? env('AWS_DEFAULT_IMAGE'),
                'InstanceType' => $params['instance_type'] ?? env('AWS_DEFAULT_INSTANCE_TYPE'),
                'MinCount' => $params['min_count'] ?? 1,
                'MaxCount' => $params['max_count'] ?? 1,
                'TagSpecifications' => [
                    [
                        'ResourceType' => 'instance',
                        'Tags' => [
                            ['Key' => 'Name', 'Value' => $params['name'] ?? 'MyInstance'],
                        ],
                    ],
                ],
            ];
            $result = $this->ec2Client->runInstances($awsParams);
            return $result;
        }
        catch(\Exception $e){
            echo "There was a problem creating the EC2 instance: {$e->getMessage()}\n";
            Log::error("There was a problem creating the EC2 instance: {$e->getMessage()}\n");
            throw $e;
        }
    }

    // /**
    //  * action that spawns an ec2 instance atomically
    //  */
    // public function terminate(
    //     array $instanceIds
    // ){
    //     try {
    //         $this->ec2->terminateInstances(['InstanceIds' => $instanceIds]);
    //     }
    //     catch(Ec2Exception $e){
    //         echo "There was a problem terminating the instances: {$e->getAwsErrorMessage()}\n";
    //         Log::error("There was a problem terminating the instances: {$e->getAwsErrorMessage()}\n");
    //         throw $e;
    //     }
    // }

}

// class MockEC2Spawner extends EC2Spawner{}