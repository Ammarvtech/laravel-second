<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Contracts\Videos\VideosContract;
use Storage;

class VideosTranscode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $que   = "arn:aws:mediaconvert:eu-west-1:783099326504:queues/Default";
    private $role  = "arn:aws:iam::783099326504:role/media";
    public  $tries = 2;
    private $title;
    private $path;
    private $ext;

    public function __construct($path, $title, $ext)
    {
      $this->title = $title;
      $this->path  = $path;
      $this->ext   = $ext;
    }

    public function handle()
    {
        $destination = "s3://tasali/videos/". $this->path . '/' . $this->title . '/';
        $input       = "s3://tasalivideos/". $this->title. '.' . $this->ext;

        $aws = \AWSContract::createClient('mediaconvert', [
            'endpoint' => env('AWS_ENDPOINT')
        ]);

        $aws->createJob(
          [
            "Queue"        => $this->que,
            "UserMetadata" => [],
            "Role"         => $this->role,
            "Settings"     => [
              "OutputGroups" => [
                [
                  "CustomName" => "name",
                  "Name"       => "Apple HLS",
                  "Outputs"    => [
                    [
                      "ContainerSettings" => [
                        "Container"    => "M3U8",
                        "M3u8Settings" => [
                          "AudioFramesPerPes"  => 4,
                          "PcrControl"         => "PCR_EVERY_PES_PACKET",
                          "PmtPid"             => 480,
                          "PrivateMetadataPid" => 503,
                          "ProgramNumber"      => 1,
                          "PatInterval"        => 0,
                          "PmtInterval"        => 0,
                          "Scte35Pid"          => 500,
                          "TimedMetadataPid"   => 502,
                          "VideoPid"           => 481,
                          "AudioPids"          => [
                            482,
                            483,
                            484,
                            485,
                            486,
                            487,
                            488,
                            489,
                            490,
                            491,
                            492,
                            493,
                            494,
                            495,
                            496,
                            497,
                            498
                          ]
                        ]
                      ],
                      "Preset"           => "System-Avc_16x9_1080p_29_97fps_8500kbps",
                      "VideoDescription" => [
                        "Width"              => 1920,
                        "ScalingBehavior"    => "DEFAULT",
                        "Height"             => 1080,
                        "VideoPreprocessors" => [
                          "Deinterlacer" => [
                            "Algorithm" => "INTERPOLATE",
                            "Mode"      => "DEINTERLACE",
                            "Control"   => "NORMAL"
                          ]
                        ],
                        "TimecodeInsertion" => "DISABLED",
                        "AntiAlias"         => "ENABLED",
                        "Sharpness"         => 50,
                        "CodecSettings"     => [
                          "Codec"        => "H_264",
                          "H264Settings" => [
                            "InterlaceMode"                       => "PROGRESSIVE",
                            "ParNumerator"                        => 1,
                            "NumberReferenceFrames"               => 3,
                            "Syntax"                              => "DEFAULT",
                            "FramerateDenominator"                => 1001,
                            "GopClosedCadence"                    => 1,
                            "HrdBufferInitialFillPercentage"      => 90,
                            "GopSize"                             => 90,
                            "Slices"                              => 1,
                            "GopBReference"                       => "DISABLED",
                            "HrdBufferSize"                       => 12750000,
                            "SlowPal"                             => "DISABLED",
                            "ParDenominator"                      => 1,
                            "SpatialAdaptiveQuantization"         => "ENABLED",
                            "TemporalAdaptiveQuantization"        => "ENABLED",
                            "FlickerAdaptiveQuantization"         => "DISABLED",
                            "EntropyEncoding"                     => "CABAC",
                            "Bitrate"                             => 8500000,
                            "FramerateControl"                    => "SPECIFIED",
                            "RateControlMode"                     => "CBR",
                            "CodecProfile"                        => "HIGH",
                            "Telecine"                            => "NONE",
                            "FramerateNumerator"                  => 30000,
                            "MinIInterval"                        => 0,
                            "AdaptiveQuantization"                => "HIGH",
                            "CodecLevel"                          => "LEVEL_4",
                            "FieldEncoding"                       => "PAFF",
                            "SceneChangeDetect"                   => "ENABLED",
                            "QualityTuningLevel"                  => "MULTI_PASS_HQ",
                            "FramerateConversionAlgorithm"        => "DUPLICATE_DROP",
                            "UnregisteredSeiTimecode"             => "DISABLED",
                            "GopSizeUnits"                        => "FRAMES",
                            "ParControl"                          => "SPECIFIED",
                            "NumberBFramesBetweenReferenceFrames" => 1,
                            "RepeatPps"                           => "DISABLED"
                          ]
                        ],
                        "AfdSignaling"      => "NONE",
                        "DropFrameTimecode" => "ENABLED",
                        "RespondToAfd"      => "NONE",
                        "ColorMetadata"     => "INSERT"
                      ],
                      "AudioDescriptions" => [
                        [
                          "AudioTypeControl" => "FOLLOW_INPUT",
                          "CodecSettings"    => [
                            "Codec"       => "AAC",
                            "AacSettings" => [
                              "AudioDescriptionBroadcasterMix" => "NORMAL",
                              "Bitrate"                        => 128000,
                              "RateControlMode"                => "CBR",
                              "CodecProfile"                   => "LC",
                              "CodingMode"                     => "CODING_MODE_2_0",
                              "RawFormat"                      => "NONE",
                              "SampleRate"                     => 48000,
                              "Specification"                  => "MPEG4"
                            ]
                          ],
                          "LanguageCodeControl" => "FOLLOW_INPUT",
                          "AudioType"           => 0
                        ]
                      ],
                      "NameModifier" => "1080p"
                    ],
                    [
                      "ContainerSettings" => [
                        "Container"    => "M3U8",
                        "M3u8Settings" => [
                          "AudioFramesPerPes"  => 4,
                          "PcrControl"         => "PCR_EVERY_PES_PACKET",
                          "PmtPid"             => 480,
                          "PrivateMetadataPid" => 503,
                          "ProgramNumber"      => 1,
                          "PatInterval"        => 0,
                          "PmtInterval"        => 0,
                          "Scte35Pid"          => 500,
                          "TimedMetadataPid"   => 502,
                          "VideoPid"           => 481,
                          "AudioPids"          => [
                            482,
                            483,
                            484,
                            485,
                            486,
                            487,
                            488,
                            489,
                            490,
                            491,
                            492,
                            493,
                            494,
                            495,
                            496,
                            497,
                            498
                          ]
                        ]
                      ],
                      "Preset"           => "System-Avc_16x9_720p_29_97fps_5000kbps",
                      "VideoDescription" => [
                        "Width"              => 1280,
                        "ScalingBehavior"    => "DEFAULT",
                        "Height"             => 720,
                        "VideoPreprocessors" => [
                          "Deinterlacer" => [
                            "Algorithm" => "INTERPOLATE",
                            "Mode"      => "DEINTERLACE",
                            "Control"   => "NORMAL"
                          ]
                        ],
                        "TimecodeInsertion" => "DISABLED",
                        "AntiAlias"         => "ENABLED",
                        "Sharpness"         => 50,
                        "CodecSettings"     => [
                          "Codec"        => "H_264",
                          "H264Settings" => [
                            "InterlaceMode"                       => "PROGRESSIVE",
                            "ParNumerator"                        => 1,
                            "NumberReferenceFrames"               => 3,
                            "Syntax"                              => "DEFAULT",
                            "FramerateDenominator"                => 1001,
                            "GopClosedCadence"                    => 1,
                            "HrdBufferInitialFillPercentage"      => 90,
                            "GopSize"                             => 90,
                            "Slices"                              => 1,
                            "GopBReference"                       => "DISABLED",
                            "HrdBufferSize"                       => 7500000,
                            "SlowPal"                             => "DISABLED",
                            "ParDenominator"                      => 1,
                            "SpatialAdaptiveQuantization"         => "ENABLED",
                            "TemporalAdaptiveQuantization"        => "ENABLED",
                            "FlickerAdaptiveQuantization"         => "DISABLED",
                            "EntropyEncoding"                     => "CABAC",
                            "Bitrate"                             => 5000000,
                            "FramerateControl"                    => "SPECIFIED",
                            "RateControlMode"                     => "CBR",
                            "CodecProfile"                        => "MAIN",
                            "Telecine"                            => "NONE",
                            "FramerateNumerator"                  => 30000,
                            "MinIInterval"                        => 0,
                            "AdaptiveQuantization"                => "HIGH",
                            "CodecLevel"                          => "LEVEL_3_1",
                            "FieldEncoding"                       => "PAFF",
                            "SceneChangeDetect"                   => "ENABLED",
                            "QualityTuningLevel"                  => "MULTI_PASS_HQ",
                            "FramerateConversionAlgorithm"        => "DUPLICATE_DROP",
                            "UnregisteredSeiTimecode"             => "DISABLED",
                            "GopSizeUnits"                        => "FRAMES",
                            "ParControl"                          => "SPECIFIED",
                            "NumberBFramesBetweenReferenceFrames" => 1,
                            "RepeatPps"                           => "DISABLED"
                          ]
                        ],
                        "AfdSignaling"      => "NONE",
                        "DropFrameTimecode" => "ENABLED",
                        "RespondToAfd"      => "NONE",
                        "ColorMetadata"     => "INSERT"
                      ],
                      "AudioDescriptions" => [
                        [
                          "AudioTypeControl" => "FOLLOW_INPUT",
                          "CodecSettings"    => [
                            "Codec"       => "AAC",
                            "AacSettings" => [
                              "AudioDescriptionBroadcasterMix" => "NORMAL",
                              "Bitrate"                        => 96000,
                              "RateControlMode"                => "CBR",
                              "CodecProfile"                   => "HEV1",
                              "CodingMode"                     => "CODING_MODE_2_0",
                              "RawFormat"                      => "NONE",
                              "SampleRate"                     => 48000,
                              "Specification"                  => "MPEG4"
                            ]
                          ],
                          "LanguageCodeControl" => "FOLLOW_INPUT",
                          "AudioType"           => 0
                        ]
                      ],
                      "NameModifier" => "720p"
                    ],
                    [
                      "ContainerSettings" => [
                        "Container"    => "M3U8",
                        "M3u8Settings" => [
                          "AudioFramesPerPes"  => 4,
                          "PcrControl"         => "PCR_EVERY_PES_PACKET",
                          "PmtPid"             => 480,
                          "PrivateMetadataPid" => 503,
                          "ProgramNumber"      => 1,
                          "PatInterval"        => 0,
                          "PmtInterval"        => 0,
                          "Scte35Pid"          => 500,
                          "TimedMetadataPid"   => 502,
                          "VideoPid"           => 481,
                          "AudioPids"          => [
                            482,
                            483,
                            484,
                            485,
                            486,
                            487,
                            488,
                            489,
                            490,
                            491,
                            492,
                            493,
                            494,
                            495,
                            496,
                            497,
                            498
                          ]
                        ]
                      ],
                      "Preset"           => "System-Avc_16x9_540p_29_97fps_3500kbps",
                      "VideoDescription" => [
                        "Width"              => 960,
                        "ScalingBehavior"    => "DEFAULT",
                        "Height"             => 540,
                        "VideoPreprocessors" => [
                          "Deinterlacer" => [
                            "Algorithm" => "INTERPOLATE",
                            "Mode"      => "DEINTERLACE",
                            "Control"   => "NORMAL"
                          ]
                        ],
                        "TimecodeInsertion" => "DISABLED",
                        "AntiAlias"         => "ENABLED",
                        "Sharpness"         => 50,
                        "CodecSettings"     => [
                          "Codec"        => "H_264",
                          "H264Settings" => [
                            "InterlaceMode"                       => "PROGRESSIVE",
                            "ParNumerator"                        => 1,
                            "NumberReferenceFrames"               => 3,
                            "Syntax"                              => "DEFAULT",
                            "FramerateDenominator"                => 1001,
                            "GopClosedCadence"                    => 1,
                            "HrdBufferInitialFillPercentage"      => 90,
                            "GopSize"                             => 90,
                            "Slices"                              => 1,
                            "GopBReference"                       => "DISABLED",
                            "HrdBufferSize"                       => 5250000,
                            "SlowPal"                             => "DISABLED",
                            "ParDenominator"                      => 1,
                            "SpatialAdaptiveQuantization"         => "ENABLED",
                            "TemporalAdaptiveQuantization"        => "ENABLED",
                            "FlickerAdaptiveQuantization"         => "DISABLED",
                            "EntropyEncoding"                     => "CABAC",
                            "Bitrate"                             => 3500000,
                            "FramerateControl"                    => "SPECIFIED",
                            "RateControlMode"                     => "CBR",
                            "CodecProfile"                        => "MAIN",
                            "Telecine"                            => "NONE",
                            "FramerateNumerator"                  => 30000,
                            "MinIInterval"                        => 0,
                            "AdaptiveQuantization"                => "HIGH",
                            "CodecLevel"                          => "LEVEL_3_1",
                            "FieldEncoding"                       => "PAFF",
                            "SceneChangeDetect"                   => "ENABLED",
                            "QualityTuningLevel"                  => "MULTI_PASS_HQ",
                            "FramerateConversionAlgorithm"        => "DUPLICATE_DROP",
                            "UnregisteredSeiTimecode"             => "DISABLED",
                            "GopSizeUnits"                        => "FRAMES",
                            "ParControl"                          => "SPECIFIED",
                            "NumberBFramesBetweenReferenceFrames" => 3,
                            "RepeatPps"                           => "DISABLED"
                          ]
                        ],
                        "AfdSignaling"      => "NONE",
                        "DropFrameTimecode" => "ENABLED",
                        "RespondToAfd"      => "NONE",
                        "ColorMetadata"     => "INSERT"
                      ],
                      "AudioDescriptions" => [
                        [
                          "AudioTypeControl" => "FOLLOW_INPUT",
                          "CodecSettings"    => [
                            "Codec" => "AAC",
                            "AacSettings" => [
                              "AudioDescriptionBroadcasterMix" => "NORMAL",
                              "Bitrate"                        => 96000,
                              "RateControlMode"                => "CBR",
                              "CodecProfile"                   => "HEV1",
                              "CodingMode"                     => "CODING_MODE_2_0",
                              "RawFormat"                      => "NONE",
                              "SampleRate"                     => 48000,
                              "Specification"                  => "MPEG4"
                            ]
                          ],
                          "LanguageCodeControl" => "FOLLOW_INPUT",
                          "AudioType"           => 0
                        ]
                      ],
                      "NameModifier" => "540p"
                    ],
                    [
                      "ContainerSettings" => [
                        "Container"    => "M3U8",
                        "M3u8Settings" => [
                          "AudioFramesPerPes"  => 4,
                          "PcrControl"         => "PCR_EVERY_PES_PACKET",
                          "PmtPid"             => 480,
                          "PrivateMetadataPid" => 503,
                          "ProgramNumber"      => 1,
                          "PatInterval"        => 0,
                          "PmtInterval"        => 0,
                          "Scte35Pid"          => 500,
                          "TimedMetadataPid"   => 502,
                          "VideoPid"           => 481,
                          "AudioPids"          => [
                            482,
                            483,
                            484,
                            485,
                            486,
                            487,
                            488,
                            489,
                            490,
                            491,
                            492,
                            493,
                            494,
                            495,
                            496,
                            497,
                            498
                          ]
                        ]
                      ],
                      "Preset"           => "System-Avc_16x9_270p_14_99fps_400kbps",
                      "VideoDescription" => [
                        "Width"              => 480,
                        "ScalingBehavior"    => "DEFAULT",
                        "Height"             => 270,
                        "VideoPreprocessors" => [
                          "Deinterlacer" => [
                            "Algorithm" => "INTERPOLATE",
                            "Mode"      => "DEINTERLACE",
                            "Control"   => "NORMAL"
                          ]
                        ],
                        "TimecodeInsertion" => "DISABLED",
                        "AntiAlias"         => "ENABLED",
                        "Sharpness"         => 50,
                        "CodecSettings"     => [
                          "Codec"        => "H_264",
                          "H264Settings" => [
                            "InterlaceMode"                       => "PROGRESSIVE",
                            "ParNumerator"                        => 1,
                            "NumberReferenceFrames"               => 3,
                            "Syntax"                              => "DEFAULT",
                            "FramerateDenominator"                => 1001,
                            "GopClosedCadence"                    => 1,
                            "HrdBufferInitialFillPercentage"      => 90,
                            "GopSize"                             => 45,
                            "Slices"                              => 1,
                            "GopBReference"                       => "DISABLED",
                            "HrdBufferSize"                       => 600000,
                            "SlowPal"                             => "DISABLED",
                            "ParDenominator"                      => 1,
                            "SpatialAdaptiveQuantization"         => "ENABLED",
                            "TemporalAdaptiveQuantization"        => "ENABLED",
                            "FlickerAdaptiveQuantization"         => "DISABLED",
                            "EntropyEncoding"                     => "CABAC",
                            "Bitrate"                             => 400000,
                            "FramerateControl"                    => "SPECIFIED",
                            "RateControlMode"                     => "CBR",
                            "CodecProfile"                        => "MAIN",
                            "Telecine"                            => "NONE",
                            "FramerateNumerator"                  => 15000,
                            "MinIInterval"                        => 0,
                            "AdaptiveQuantization"                => "HIGH",
                            "CodecLevel"                          => "LEVEL_3_1",
                            "FieldEncoding"                       => "PAFF",
                            "SceneChangeDetect"                   => "ENABLED",
                            "QualityTuningLevel"                  => "MULTI_PASS_HQ",
                            "FramerateConversionAlgorithm"        => "DUPLICATE_DROP",
                            "UnregisteredSeiTimecode"             => "DISABLED",
                            "GopSizeUnits"                        => "FRAMES",
                            "ParControl"                          => "SPECIFIED",
                            "NumberBFramesBetweenReferenceFrames" => 3,
                            "RepeatPps"                           => "DISABLED"
                          ]
                        ],
                        "AfdSignaling"      => "NONE",
                        "DropFrameTimecode" => "ENABLED",
                        "RespondToAfd"      => "NONE",
                        "ColorMetadata"     => "INSERT"
                      ],
                      "AudioDescriptions" => [
                        [
                          "AudioTypeControl" => "FOLLOW_INPUT",
                          "CodecSettings"    => [
                            "Codec"       => "AAC",
                            "AacSettings" => [
                              "AudioDescriptionBroadcasterMix" => "NORMAL",
                              "Bitrate"                        => 64000,
                              "RateControlMode"                => "CBR",
                              "CodecProfile"                   => "HEV1",
                              "CodingMode"                     => "CODING_MODE_2_0",
                              "RawFormat"                      => "NONE",
                              "SampleRate"                     => 48000,
                              "Specification"                  => "MPEG4"
                            ]
                          ],
                          "LanguageCodeControl" => "FOLLOW_INPUT",
                          "AudioType"           => 0
                        ]
                      ],
                      "NameModifier" => "270p"
                    ]
                  ],
                  "OutputGroupSettings" => [
                    "Type"             => "HLS_GROUP_SETTINGS",
                    "HlsGroupSettings" => [
                      "ManifestDurationFormat" => "INTEGER",
                      "SegmentLength"          => 10,
                      "TimedMetadataId3Period" => 10,
                      "CaptionLanguageSetting" => "OMIT",
                      "Destination"            => $destination,
                      "TimedMetadataId3Frame"  => "PRIV",
                      "CodecSpecification"     => "RFC_4281",
                      "OutputSelection"        => "MANIFESTS_AND_SEGMENTS",
                      "ProgramDateTimePeriod"  => 600,
                      "MinSegmentLength"       => 0,
                      "DirectoryStructure"     => "SINGLE_DIRECTORY",
                      "ProgramDateTime"        => "EXCLUDE",
                      "SegmentControl"         => "SEGMENTED_FILES",
                      "ManifestCompression"    => "NONE",
                      "ClientCache"            => "ENABLED",
                      "StreamInfResolution"    => "INCLUDE"
                    ]
                  ]
                ]
              ],
              "AdAvailOffset" => 0,
              "Inputs"        => [
                [
                  "AudioSelectors" => [
                    "Audio Selector 1" => [
                      "Offset"           => 0,
                      "DefaultSelection" => "DEFAULT",
                      "ProgramSelection" => 1
                    ]
                  ],
                  "VideoSelector" => [
                    "ColorSpace" => "FOLLOW"
                  ],
                  "FilterEnable"   => "AUTO",
                  "PsiControl"     => "USE_PSI",
                  "FilterStrength" => 0,
                  "DeblockFilter"  => "DISABLED",
                  "DenoiseFilter"  => "DISABLED",
                  "TimecodeSource" => "EMBEDDED",
                  "FileInput"      => $input,
                ]
              ]
            ]
          ]
        );
    }
}
