<?php
namespace Mill\Tests\Generator;

use Mill\Generator\Changelog;
use Mill\Tests\TestCase;

class ChangelogTest extends TestCase
{
    /**
     * @dataProvider providerTestGeneration
     * @param boolean $private_objects
     * @param array $capabilities
     * @param array $expected
     * @return void
     */
    public function testGeneration($private_objects, $capabilities, $expected)
    {
        $generator = new Changelog($this->getConfig());
        $generator->setLoadPrivateDocs($private_objects);
        $generator->setLoadCapabilityDocs($capabilities);
        $changelog = $generator->generate();

        $this->assertSame(array_keys($expected), array_keys($changelog));

        foreach ($expected as $version => $expected_changes) {
            $this->assertSame(
                array_keys($expected_changes),
                array_keys($changelog[$version]),
                'Change for v' . $version . ' does not have the same array keys.'
            );

            foreach ($expected_changes as $section => $changes) {
                $this->assertSame(
                    $changes,
                    $changelog[$version][$section],
                    'The `' . $section . '` changes for v' . $version . ' don\'t match up.'
                );
            }
        }
    }

    public function testJsonGeneration()
    {
        $generator = new Changelog($this->getConfig());
        $changelog = $generator->generateJson();
        $changelog = json_decode($changelog, true);

        // We don't need to test the full functionality of the JSON extension to the Changelog generator, since that's
        // being done in `Generator\Changelog\JsonTest`, we just want to make sure that we at least have the expected
        // array keys.
        $this->assertSame([
            '1.1.3',
            '1.1.2',
            '1.1.1',
            '1.1'
        ], array_keys($changelog));
    }

    /**
     * @return array
     */
    public function providerTestGeneration()
    {
        // Save us the effort of copy and pasting the same base actions over and over.
        $actions = [
            '1.1.3' => [
                '/movie/{id}' => [
                    'throws' => [
                        '2e302f7f79' => [
                            [
                                'method' => 'GET',
                                'uri' => '/movie/{id}',
                                'http_code' => '404 Not Found',
                                'representation' => 'Error',
                                'description' => 'For no reason.'
                            ],
                            [
                                'method' => 'GET',
                                'uri' => '/movie/{id}',
                                'http_code' => '404 Not Found',
                                'representation' => 'Error',
                                'description' => 'For some other reason.'
                            ]
                        ]
                    ]
                ],
                '/movies' => [
                    'return' => [
                        '3781891d58' => [
                            [
                                'method' => 'POST',
                                'uri' => '/movies',
                                'http_code' => '201 Created',
                                'representation' => false
                            ]
                        ]
                    ]
                ],
                '/movies/{id}' => [
                    'return' => [
                        '162944fa14' => [
                            [
                                'method' => 'PATCH',
                                'uri' => '/movies/{id}',
                                'http_code' => '202 Accepted',
                                'representation' => 'Movie'
                            ]
                        ]
                    ],
                    'throws' => [
                        'e7dc298139' => [
                            [
                                'method' => 'GET',
                                'uri' => '/movies/{id}',
                                'http_code' => '404 Not Found',
                                    'representation' => 'Error',
                                'description' => 'For no reason.'
                            ],
                            [
                                'method' => 'GET',
                                'uri' => '/movies/{id}',
                                'http_code' => '404 Not Found',
                                'representation' => 'Error',
                                'description' => 'For some other reason.'
                            ]
                        ],
                        '162944fa14' => [
                            [
                                'method' => 'PATCH',
                                'uri' => '/movies/{id}',
                                'http_code' => '404 Not Found',
                                'representation' => 'Error',
                                'description' => 'If the trailer URL could not be validated.'
                            ]
                        ]
                    ]
                ]
            ],
            '1.1.2' => [
                '/movie/{id}' => [
                    'content_type' => [
                        '979fc6e97f' => [
                            [
                                'method' => 'GET',
                                'uri' => '/movie/{id}',
                                'content_type' => 'application/mill.example.movie'
                            ]
                        ]
                    ]
                ],
                '/movies' => [
                    'content_type' => [
                        '979fc6e97f' => [
                            [
                                'method' => 'GET',
                                'uri' => '/movies',
                                'content_type' => 'application/mill.example.movie'
                            ]
                        ],
                        '066564ef49' => [
                            [
                                'method' => 'POST',
                                'uri' => '/movies',
                                'content_type' => 'application/mill.example.movie'
                            ]
                        ]
                    ]
                ],
                '/movies/{id}' => [
                    'content_type' => [
                        '979fc6e97f' => [
                            [
                                'method' => 'GET',
                                'uri' => '/movies/{id}',
                                'content_type' => 'application/mill.example.movie'
                            ]
                        ],
                        'f4628f751a' => [
                            [
                                'method' => 'PATCH',
                                'uri' => '/movies/{id}',
                                'content_type' => 'application/mill.example.movie'
                            ]
                        ]
                    ]
                ],
                '/theaters' => [
                    'content_type' => [
                        '979fc6e97f' => [
                            [
                                'method' => 'GET',
                                'uri' => '/theaters',
                                'content_type' => 'application/mill.example.theater'
                            ]
                        ],
                        '066564ef49' => [
                            [
                                'method' => 'POST',
                                'uri' => '/theaters',
                                'content_type' => 'application/mill.example.theater'
                            ]
                        ]
                    ]
                ],
                '/theaters/{id}' => [
                    'content_type' => [
                        '979fc6e97f' => [
                            [
                                'method' => 'GET',
                                'uri' => '/theaters/{id}',
                                'content_type' => 'application/mill.example.theater'
                            ]
                        ],
                        'f4628f751a' => [
                            [
                                'method' => 'PATCH',
                                'uri' => '/theaters/{id}',
                                'content_type' => 'application/mill.example.theater'
                            ]
                        ]
                    ]
                ]
            ],
            '1.1.1' => [
                '/movies/{id}' => [
                    'param' => [
                        '162944fa14' => [
                            [
                                'method' => 'PATCH',
                                'uri' => '/movies/{id}',
                                'parameter' => 'imdb',
                                'description' => 'IMDB URL'
                            ]
                        ]
                    ]
                ]
            ],
            '1.1' => [
                '/movies' => [
                    'param' => [
                        '776d02bb83' => [
                            [
                                'method' => 'GET',
                                'uri' => '/movies',
                                'parameter' => 'page',
                                'description' => 'Page of results to pull.'
                            ]
                        ],
                        '3781891d58' => [
                            [
                                'method' => 'POST',
                                'uri' => '/movies',
                                'parameter' => 'imdb',
                                'description' => 'IMDB URL'
                            ],
                            [
                                'method' => 'POST',
                                'uri' => '/movies',
                                'parameter' => 'trailer',
                                'description' => 'Trailer URL'
                            ]
                        ]
                    ]
                ],
                '/movies/{id}' => [
                    'action' => [
                        'd81e7058dd' => [
                            [
                                'method' => 'PATCH',
                                'uri' => '/movies/{id}'
                            ],
                            [
                                'method' => 'DELETE',
                                'uri' => '/movies/{id}'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $representations = [
            '1.1.3' => [
                'Movie' => [
                    'ba8ac44626' => [
                        [
                            'field' => 'external_urls.tickets',
                            'representation' => 'Movie'
                        ]
                    ]
                ]
            ],
            '1.1' => [
                'Movie' => [
                    'ba8ac44626' => [
                        [
                            'field' => 'external_urls',
                            'representation' => 'Movie'
                        ],
                        [
                            'field' => 'external_urls.imdb',
                            'representation' => 'Movie'
                        ],
                        [
                            'field' => 'external_urls.tickets',
                            'representation' => 'Movie'
                        ],
                        [
                            'field' => 'external_urls.trailer',
                            'representation' => 'Movie'
                        ]
                    ]
                ],
                'Theater' => [
                    '4034255a2c' => [
                        [
                            'field' => 'website',
                            'representation' => 'Theater'
                        ]
                    ]
                ]
            ]
        ];

        return [
            // Complete changelog. All documentation parsed.
            'complete-changelog' => [
                'private_objects' => true,
                'capabilities' => null,
                'expected' => [
                    '1.1.3' => [
                        '_details' => [
                            'release_date' => '2017-05-27',
                            'description' => 'Changed up the responses for `/movie/{id}`, `/movies/{id}` and `/movies`.'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movie/{id}' => [
                                        Changelog::CHANGE_ACTION_THROWS => [
                                            '2e302f7f79' => $actions['1.1.3']['/movie/{id}']['throws']['2e302f7f79']
                                        ]
                                    ],
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_THROWS => [
                                            'e7dc298139' => $actions['1.1.3']['/movies/{id}']['throws']['e7dc298139'],
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['throws']['162944fa14']
                                        ],
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['return']['162944fa14']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '3781891d58' => $actions['1.1.3']['/movies']['return']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1.3']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.2' => [
                        '_details' => [
                            'release_date' => '2017-04-01'
                        ],
                        'changed' => [
                            'resources' => [
                                'Movies' => [
                                    '/movie/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movie/{id}']['content_type']['979fc6e97f']
                                        ]
                                    ],
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' => $actions['1.1.2']['/movies']['content_type']['979fc6e97f'],
                                            '066564ef49' => $actions['1.1.2']['/movies']['content_type']['066564ef49']
                                        ]
                                    ]
                                ],
                                'Theaters' => [
                                    '/theaters/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/theaters' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters']['content_type']['979fc6e97f'],
                                            '066564ef49' => $actions['1.1.2']['/theaters']['content_type']['066564ef49']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.1' => [
                        '_details' => [
                            'release_date' => '2017-03-01'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '162944fa14' => $actions['1.1.1']['/movies/{id}']['param']['162944fa14']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1' => [
                        '_details' => [
                            'release_date' => '2017-02-01'
                        ],
                        'added' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ],
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION => [
                                            'd81e7058dd' => $actions['1.1']['/movies/{id}']['action']['d81e7058dd']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '776d02bb83' => $actions['1.1']['/movies']['param']['776d02bb83'],
                                            '3781891d58' => $actions['1.1']['/movies']['param']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Theater' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        '4034255a2c' => $representations['1.1']['Theater']['4034255a2c']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // Changelog with public-only parsed docs and all capabilities.
            'changelog-public-docs-with-all-capabilities' => [
                'private_objects' => false,
                'capabilities' => [
                    'BUY_TICKETS',
                    'DELETE_CONTENT',
                    'FEATURE_FLAG',
                    'MOVIE_RATINGS'
                ],
                'expected' => [
                    '1.1.3' => [
                        '_details' => [
                            'release_date' => '2017-05-27',
                            'description' => 'Changed up the responses for `/movie/{id}`, `/movies/{id}` and `/movies`.'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_THROWS => [
                                            'e7dc298139' => $actions['1.1.3']['/movies/{id}']['throws']['e7dc298139'],
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['throws']['162944fa14']
                                        ],
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['return']['162944fa14']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '3781891d58' => $actions['1.1.3']['/movies']['return']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1.3']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.2' => [
                        '_details' => [
                            'release_date' => '2017-04-01'
                        ],
                        'changed' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' => $actions['1.1.2']['/movies']['content_type']['979fc6e97f'],
                                            '066564ef49' => $actions['1.1.2']['/movies']['content_type']['066564ef49']
                                        ]
                                    ]
                                ],
                                'Theaters' => [
                                    '/theaters/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/theaters' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters']['content_type']['979fc6e97f'],
                                            '066564ef49' =>
                                                $actions['1.1.2']['/theaters']['content_type']['066564ef49']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.1' => [
                        '_details' => [
                            'release_date' => '2017-03-01'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '162944fa14' => $actions['1.1.1']['/movies/{id}']['param']['162944fa14']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1' => [
                        '_details' => [
                            'release_date' => '2017-02-01'
                        ],
                        'added' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ],
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION => [
                                            'd81e7058dd' => $actions['1.1']['/movies/{id}']['action']['d81e7058dd']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '776d02bb83' => $actions['1.1']['/movies']['param']['776d02bb83'],
                                            '3781891d58' => $actions['1.1']['/movies']['param']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Theater' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        '4034255a2c' => $representations['1.1']['Theater']['4034255a2c']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // Changelog with public-only parsed docs and unmatched capabilities
            'changelog-public-docs-with-unmatched-capabilities' => [
                'private_objects' => false,
                'capabilities' => [
                    'BUY_TICKETS',
                    'FEATURE_FLAG'
                ],
                'expected' => [
                    '1.1.3' => [
                        '_details' => [
                            'release_date' => '2017-05-27',
                            'description' => 'Changed up the responses for `/movie/{id}`, `/movies/{id}` and `/movies`.'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_THROWS => [
                                            'e7dc298139' => $actions['1.1.3']['/movies/{id}']['throws']['e7dc298139'],
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['throws']['162944fa14']
                                        ],
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['return']['162944fa14']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '3781891d58' => $actions['1.1.3']['/movies']['return']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1.3']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.2' => [
                        '_details' => [
                            'release_date' => '2017-04-01'
                        ],
                        'changed' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movies']['content_type']['979fc6e97f'],
                                            '066564ef49' =>
                                                $actions['1.1.2']['/movies']['content_type']['066564ef49']
                                        ]
                                    ]
                                ],
                                'Theaters' => [
                                    '/theaters/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/theaters' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters']['content_type']['979fc6e97f'],
                                            '066564ef49' =>
                                                $actions['1.1.2']['/theaters']['content_type']['066564ef49']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.1' => [
                        '_details' => [
                            'release_date' => '2017-03-01'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '162944fa14' => $actions['1.1.1']['/movies/{id}']['param']['162944fa14']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1' => [
                        '_details' => [
                            'release_date' => '2017-02-01'
                        ],
                        'added' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ],
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION => [
                                            'd81e7058dd' => call_user_func(
                                                function () use ($actions) {
                                                    $actions = $actions['1.1']['/movies/{id}']['action']['d81e7058dd'];

                                                    // Remove the `DELETE` method from `/movies/{id}`, since that
                                                    // shouldn't be available under these conditions.
                                                    unset($actions[1]);
                                                    return $actions;
                                                }
                                            )
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '776d02bb83' => $actions['1.1']['/movies']['param']['776d02bb83'],
                                            '3781891d58' => $actions['1.1']['/movies']['param']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Theater' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        '4034255a2c' => $representations['1.1']['Theater']['4034255a2c']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ],

            // Changelog with public-only parsed docs and matched capabilities
            'changelog-public-docs-with-matched-capabilities' => [
                'private_objects' => false,
                'capabilities' => [
                    'DELETE_CONTENT'
                ],
                'expected' => [
                    '1.1.3' => [
                        '_details' => [
                            'release_date' => '2017-05-27',
                            'description' => 'Changed up the responses for `/movie/{id}`, `/movies/{id}` and `/movies`.'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_THROWS => [
                                            'e7dc298139' => $actions['1.1.3']['/movies/{id}']['throws']['e7dc298139'],
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['throws']['162944fa14']
                                        ],
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['return']['162944fa14']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '3781891d58' => $actions['1.1.3']['/movies']['return']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1.3']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.2' => [
                        '_details' => [
                            'release_date' => '2017-04-01'
                        ],
                        'changed' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movies']['content_type']['979fc6e97f'],
                                            '066564ef49' =>
                                                $actions['1.1.2']['/movies']['content_type']['066564ef49']
                                        ]
                                    ]
                                ],
                                'Theaters' => [
                                    '/theaters/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/theaters' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters']['content_type']['979fc6e97f'],
                                            '066564ef49' =>
                                                $actions['1.1.2']['/theaters']['content_type']['066564ef49']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.1' => [
                        '_details' => [
                            'release_date' => '2017-03-01'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '162944fa14' => $actions['1.1.1']['/movies/{id}']['param']['162944fa14']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1' => [
                        '_details' => [
                            'release_date' => '2017-02-01'
                        ],
                        'added' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ],
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION => [
                                            'd81e7058dd' => $actions['1.1']['/movies/{id}']['action']['d81e7058dd']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '776d02bb83' => $actions['1.1']['/movies']['param']['776d02bb83'],
                                            '3781891d58' => $actions['1.1']['/movies']['param']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Theater' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        '4034255a2c' => $representations['1.1']['Theater']['4034255a2c']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            // Changelog with public-only parsed docs
            'changelog-public-docs-with-unmatched-capabilities' => [
                'private_objects' => false,
                'capabilities' => [],
                'expected' => [
                    '1.1.3' => [
                        '_details' => [
                            'release_date' => '2017-05-27',
                            'description' => 'Changed up the responses for `/movie/{id}`, `/movies/{id}` and `/movies`.'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_THROWS => [
                                            'e7dc298139' => $actions['1.1.3']['/movies/{id}']['throws']['e7dc298139'],
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['throws']['162944fa14']
                                        ],
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '162944fa14' => $actions['1.1.3']['/movies/{id}']['return']['162944fa14']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_RETURN => [
                                            '3781891d58' => $actions['1.1.3']['/movies']['return']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1.3']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.2' => [
                        '_details' => [
                            'release_date' => '2017-04-01',
                        ],
                        'changed' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/movies/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' => $actions['1.1.2']['/movies']['content_type']['979fc6e97f'],
                                            '066564ef49' => $actions['1.1.2']['/movies']['content_type']['066564ef49']
                                        ]
                                    ]
                                ],
                                'Theaters' => [
                                    '/theaters/{id}' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['979fc6e97f'],
                                            'f4628f751a' =>
                                                $actions['1.1.2']['/theaters/{id}']['content_type']['f4628f751a']
                                        ]
                                    ],
                                    '/theaters' => [
                                        Changelog::CHANGE_CONTENT_TYPE => [
                                            '979fc6e97f' =>
                                                $actions['1.1.2']['/theaters']['content_type']['979fc6e97f'],
                                            '066564ef49' =>
                                                $actions['1.1.2']['/theaters']['content_type']['066564ef49']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1.1' => [
                        '_details' => [
                            'release_date' => '2017-03-01'
                        ],
                        'added' => [
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '162944fa14' => $actions['1.1.1']['/movies/{id}']['param']['162944fa14']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '1.1' => [
                        '_details' => [
                            'release_date' => '2017-02-01',
                        ],
                        'added' => [
                            'representations' => [
                                'Movie' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        'ba8ac44626' => $representations['1.1']['Movie']['ba8ac44626']
                                    ]
                                ]
                            ],
                            'resources' => [
                                'Movies' => [
                                    '/movies/{id}' => [
                                        Changelog::CHANGE_ACTION => [
                                            'd81e7058dd' => call_user_func(
                                                function () use ($actions) {
                                                    $hash = 'd81e7058ddfce86beb09ddb2a2461ea16d949637';
                                                    $actions = $actions['1.1']['/movies/{id}']['action']['d81e7058dd'];

                                                    // Remove the `DELETE` method from `/movies/{id}`, since that
                                                    // shouldn't be available under these conditions.
                                                    unset($actions[1]);
                                                    return $actions;
                                                }
                                            )
                                        ]
                                    ],
                                    '/movies' => [
                                        Changelog::CHANGE_ACTION_PARAM => [
                                            '776d02bb83' => $actions['1.1']['/movies']['param']['776d02bb83'],
                                            '3781891d58' => $actions['1.1']['/movies']['param']['3781891d58']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'removed' => [
                            'representations' => [
                                'Theater' => [
                                    Changelog::CHANGE_REPRESENTATION_DATA => [
                                        '4034255a2c' => $representations['1.1']['Theater']['4034255a2c']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
