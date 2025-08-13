<?php
namespace App\Services;
use App\Models\EC2Product;
use Illuminate\Http\Request;
use Aws\Ec2\Ec2Client;
use Aws\Ec2\Ec2Exception;
use Illuminate\Support\Facades\Log;
use Aws\Result;
use Aws\Exception\AwsException;
use App\Services\EC2Service;

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

    public function terminate(array $instanceIds)
    {
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
        ];
    }

    // Align with AWS runInstances response: top-level 'Instances'
    return new Result([
        'Instances' => $instances,
    ]);
}