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
namespace Contus\Audio\Repositories;

use Contus\Base\Repository as BaseRepository;
use Contus\Video\Contracts\IAWSUploadRepository;
use Aws\S3\S3Client;
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Contus\Video\Models\TranscodedVideo;
use Contus\Video\Models\VideoPreset;
use Contus\Base\Helpers\StringLiterals;
use Contus\Video\Models\Video;

class AWSUploadRepository extends BaseRepository implements IAWSUploadRepository {
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
            'version' => env('AWS_VERSION'), 
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
     * Function to upload a file to S3 bucket.
     *
     * @param string $file
     * The file to be uploaded with its path.
     * @param string $bucket
     * The name of the S3 bucket.
     * @param string $key
     * The name of the output file.
     * @return boolean True on success and False on failure.
     * @see \Contus\Video\Contracts\IAWSUploadRepository::uploadFileToS3()
     */
    public function uploadFileToS3($file, $key, $type = "") {
        $permission = "";
        if($type == 'images' && $type !== ""){
            $key = $type.'/'.$key;
            $permission = 'public-read';
        } 
        $info = pathinfo($file);
        $split = explode('/',$file);
        $fromName = $split[count($split)-2]; 
        if($info['extension'] == "mp3" && $fromName == 'subtitle'){
          $key = 'mp3/'.$key;
          $permission = 'public-read';
        } 
        $client = $this->awsS3Client; 
        $awsS3Bucket = env('AWS_BUCKET');

        $result = $client->putObject ( array ('Bucket' => $awsS3Bucket,'Key' => $key,'folder'=>'images','SourceFile' => $file,'ServerSideEncryption' => 'AES256','ACL' => $permission ) );
        $isResult = false;
        if ($result ['ObjectURL']) {
            /**
             * Save the url of the file in S3
             */
            $isResult = $result ['ObjectURL'];
        }
        return $isResult;
    }
    /**
     * Function to upload converted files to S3 bucket.
     *
     * @param string $source
     * The source is an upload file path.
     * @param string $file
     * The file to be uploaded with its path.
     * @param string $randomFileDir
     * Which is folder name to be create into s3
     * @return string $newname.
     * The file name which is need to be create in s3
     */
    public function uploadConvertedFiles($source,$file,$randomFileDir,$newname = ''){
      $s3Client = $this->awsS3Client;
      $awsS3Bucket = env('AWS_BUCKET');
      $s3Client->putObject(array(
        'Bucket' => $awsS3Bucket,
        'SourceFile' => $source . "/" . $file,
        'Key' => 'FFMPEG/' . $randomFileDir . '/' . $file,
        'ACL' => 'public-read',
        'ServerSideEncryption' => 'AES256'
      ));
    }
    /**
     * Function to transcode a file using AWS Elastic transcoder.
     *
     * @param string $pipelineId
     * The pipeline id of the AWS Elastic Transcoder.
     * @param string $inputFile
     * The name of the input file in the S3 bucket.
     * @param string $outputSlug
     * The output slug which will be appended to the name of the output files.
     * @param integer $videoID
     * The id of the video in the database.
     * @return string bool job id returned from the elastic transcoder on success and False on failure.
     * @see \Contus\Video\Contracts\IAWSUploadRepository::transcodeFile()
     */
    public function transcodeFile($pipelineId, $inputFile, $outputSlug, $videoID, $creatorID) {
        $client = $this->awsETClient;

        /**
         * Get active presets from the database.
         */
        $presets = $this->getActivePresets ();
        $video = Video::find ( $videoID );
        $outputs = $transcodePlaylist = array ();
        $playlistName = '';
        $outputKey = [ ];
        foreach ( $presets as $preset ) {
            $videoKey = ($preset [StringLiterals::FORMAT] == 'ts') ? 'video-' . $preset [StringLiterals::AWS_ID] : 'video-' . $preset [StringLiterals::AWS_ID] . '.' . $preset [StringLiterals::FORMAT];
            $output = array ('Key' => $videoKey,'ThumbnailPattern' => $preset [StringLiterals::FORMAT].'/thumb-' . $preset [StringLiterals::AWS_ID] . '-{count}','Rotate' => 'auto','PresetId' => $preset [StringLiterals::AWS_ID] );

            /**
             * Check if the format is fmp4.
             * If yes, then add a playlist for the video.
             * Playlist is mandatory for streaming formats like fmp4.
             */
            if ($preset [StringLiterals::FORMAT] == "fmp4") {
                if (strpos ( $preset ['name'], 'Smooth' ) !== false) {
                    $playlistFormat = 'Smooth';
                }
                if (strpos ( $preset ['name'], 'MPEG-Dash' ) !== false) {
                    $playlistFormat = 'MPEG-DASH';
                }
                $transcodePlaylist [] = array ('Name' => $videoKey . '-playlist','Format' => $playlistFormat,'OutputKeys' => array ($videoKey ) );
            /**
             * SegmentDuration is mandatory for fmp4 video.
             */
            } else if ($preset [StringLiterals::FORMAT] == "ts") {
                $output ['SegmentDuration'] = '5';
                $outputKey [] = $videoKey;
            }
            $outputs [] = $output;
        }

        $playlistName = 'playlist';
        $transcodePlaylist = [ [ 'Name' => $playlistName,'Format' => 'HLSv3','OutputKeys' => $outputKey,'HlsContentProtection' => [ 'Method' => 'aes-128','KeyStoragePolicy' => 'WithVariantPlaylists' ] ] ];

        /**
         * Create a job in AWS Elastic Transcoder.
         */
        $result = $client->createJob ( array ('PipelineId' => $pipelineId,'Input' => array ('Key' => $inputFile ),'Outputs' => $outputs,'OutputKeyPrefix' => 'output/' . $outputSlug . '/','Playlists' => $transcodePlaylist ) );
        if ($result ['Job']) {
            /**
             * Save the details of the transcoded files.
             */
            
            $jobId = $result ['Job'] ['Id'];
            $awsRegion = env('AWS_REGION');
            $awsS3Bucket = env('AWS_BUCKET');
            $awsBaseUrl = env('AWS_BUCKET_URL');

            if (! empty ( $playlistName )) {
                $videoURL = $awsBaseUrl. 'output/' . $outputSlug .'/'. $playlistName . '.m3u8';
                $thumbURL = 'https://s3.' . $awsRegion . '.amazonaws.com/' . $awsS3Bucket . '/output/' . $outputSlug . '/thumb-' . $presets [0] [StringLiterals::AWS_ID] . '-00001.' . $presets [0] ['thumbnail_format'];
                $video->hls_playlist_url = $videoURL;
                $video->aws_prefix = 'output/' . $outputSlug;
                $video->save();
            } else {
                foreach ( $presets as $preset ) {
                    $videoURL = 'https://s3.' . $awsRegion . '.amazonaws.com/' . $awsS3Bucket . '/output/' . $outputSlug . '/video-' . $preset [StringLiterals::AWS_ID] . '.' . $preset [StringLiterals::FORMAT];
                    $thumbURL = 'https://s3.' . $awsRegion . '.amazonaws.com/' . $awsS3Bucket . '/output/' . $outputSlug . '/thumb-' . $preset [StringLiterals::AWS_ID] . '-00001.' . $preset ['thumbnail_format'];
                    $this->transcodedVideo = new TranscodedVideo ();
                    $this->transcodedVideo->video_id = $videoID;
                    $this->transcodedVideo->preset_id = $preset ['id'];
                    $this->transcodedVideo->video_url = $videoURL;
                    $this->transcodedVideo->thumb_url = $thumbURL;
                    $this->transcodedVideo->is_active = 1;
                    $this->transcodedVideo->creator_id = $creatorID;
                    $this->transcodedVideo->save ();
                }
            }
            return $jobId;
        } else {
            return false;
        }
    }
    /**
     * Function to get the status of a transcode job from AWS.
     *
     * @param string $jobId
     * The id of the job whose status is to be fetched.
     * @return boolean string status if the retrieval is successful and false if not.
     */
    public function getJobStatus($jobId) {
        $client = $this->awsETClient;
        $result = $client->readJob ( array ('Id' => $jobId ) );
        if ($result ['Job']) {
            return $result ['Job'] ['Status'];
        } else {
            return false;
        }
    }
    /**
     * Function to get all active presets from the database.
     *
     * @return array All active presets from the database.
     */
    public function getActivePresets() {
        return $this->videoPreset->where ( 'is_active', 1 )->get ();
    }
    /**
    *Function to delete profile picture from s3bucket
    *@param string $imageName
    *The name of the image to be deleted
    *@return boolean status if deleted and false if not.
    */
    public function deleteProfileImage_s3butcket($imageName){ 
        $client = $this->awsS3Client;
        
        $awsS3Bucket = env('AWS_BUCKET');
        
        $client->deleteObject(array(
        'Bucket' => $awsS3Bucket,
        'Key'    => $imageName
        ));
        return true;
   }

    public function getAWSProgressPercent($output) {
        if(!empty($output['Outputs'])) {
            $outputArray = $output['Outputs'];
            $statusArray = [];
            $completedCount = 0;
            $totalCount = count($outputArray);
            if($totalCount > 0) {
                foreach ($outputArray as $item) {
                  if ($item['Status'] === 'Complete') {
                    $completedCount++;
                  }
                }
            }
        }

        return round((($completedCount / $totalCount) * 100 ));
    }
}
