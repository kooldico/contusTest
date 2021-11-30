<?php

/**
 * AWS Upload Repository
 *
 * To manage the functionalities related to aws upload and transcoding
 *
 * @name AWSUploadRepository
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Repositories;

use Contus\Base\Repository as BaseRepository;
use Aws\S3\S3Client;
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Contus\Video\Models\TranscodedVideo;
use Contus\Video\Models\VideoPreset;


class AWSUploadRepository extends BaseRepository {
    public $transcodedVideo;
    public $awsETClient;
    public $awsS3Client;
    public $videoPreset;

    /**
     * Construct method initialization
     *
     * Validation rule for user verification code and forgot password.
     */
    public function __construct(TranscodedVideo $transcodedVideo, VideoPreset $videoPreset) {
        parent::__construct ();
        $this->transcodedVideo = $transcodedVideo;
        $this->awsETClient = $this->getAWSClient ( 'ET' );
        $this->awsS3Client = $this->getAWSClient ( 'S3' );
        $this->videoPreset = $videoPreset;
    }
    /**
     * Function to get AWS client instance.
     *
     * @return Ambigous <\Aws\static, \Aws\ElasticTranscoder\ElasticTranscoderClient>
     */
    public function getAWSClient($clientType) {
        $credentials = array (
            'region' => env('AWS_REGION'), 
            'version' => 'latest', 
            'credentials' => [
                'key' => env('AWS_KEY'), 
                'secret' => env('AWS_SECRET')
            ]
        );

        if ($clientType == 'ET') {
            return ElasticTranscoderClient::factory ( $credentials );
        } else {
            return S3Client::factory ( $credentials );
        }
    }
    /**
     * Function to get all active presets from the database.
     *
     * @return array All active presets from the database.
     */
     public function fetchFileFromS3Bucket($key){
            $s3Client = $this->awsS3Client;
            $awsS3Bucket = env('AWS_BUCKET');
            $response = $s3Client->doesObjectExist($awsS3Bucket,$key);
            if(!$response){
            return false;
            }else {
            return $s3Client->getObject(array(
            'Bucket' => $awsS3Bucket,
            'Key' => $key,
            ));
            }
     }
}
