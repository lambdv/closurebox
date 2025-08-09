<?php
namespace App\Services;
use App\Models\EC2Product;
use Illuminate\Http\Request;
use Aws\Ec2\Ec2Client;
use Aws\Ec2\Ec2Exception;
use Illuminate\Support\Facades\Log;
use Aws\Result;

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

            //only run in production mode
            if(env('INFRA_MODE') == true && env('APP_ENV') == 'production'){ 
                $result = $this->ec2Client->runInstances($awsParams);
            }
            else {
                //fake the result for testing
                $result = fakeRunInstancesResult();
            }
            
            return $result;
        }
        catch(\Exception $e){
            echo "There was a problem creating the EC2 instance: {$e->getMessage()}\n";
            Log::error("There was a problem creating the EC2 instance: {$e->getMessage()}\n");
            throw $e;
        }
    }

        /**
     * action that spawns an ec2 instance atomically
     */
    public function terminate(
        array $instanceIds
    ){
        try {
            $this->ec2Client->terminateInstances(['InstanceIds' => $instanceIds]);
        }
        catch(\Exception $e){
            echo "There was a problem terminating the instances: {$e->getMessage()}\n";
            Log::error("There was a problem terminating the instances: {$e->getMessage()}\n");
            throw $e;
        }
    }
}


    



class MockEC2Service extends EC2Service{
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


                //fake the result for testing
            $result = fakeRunInstancesResult();
           
            
            return $result;
        }
        catch(\Exception $e){
            echo "There was a problem creating the EC2 instance: {$e->getMessage()}\n";
            Log::error("There was a problem creating the EC2 instance: {$e->getMessage()}\n");
            throw $e;
        }
}
}

function fakeRunInstancesResult(int $count = 1): Result
{
    $instances = [];

    for ($i = 0; $i < $count; $i++) {
        $instances[] = [
            'InstanceId'   => 'i-' . bin2hex(random_bytes(8)),
            'ImageId'      => 'ami-' . bin2hex(random_bytes(8)),
            'State'        => [
                'Code' => 0,
                'Name' => 'pending'
            ],
            'InstanceType' => 't3.small',
            'LaunchTime'   => gmdate('Y-m-d\TH:i:s.000\Z'),
            'Monitoring'   => [
                'State' => 'disabled'
            ],
            'PrivateIpAddress' => '192.168.' . rand(0, 255) . '.' . rand(1, 254),
            'PublicIpAddress'  => '54.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
            // You can add more fields as needed to match AWS response
        ];
    }

    return new Result([
        'Reservations' => [
            [
                'OwnerId'       => '123456789012',
                'ReservationId' => 'r-' . bin2hex(random_bytes(8)),
                'Instances'     => $instances
            ]
        ]
    ]);
}