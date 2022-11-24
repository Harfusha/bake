<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.1.0
 * @license   https://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Test\App\Controller;

/**
 * PostsController class
 */
class PostsController extends AppController
{
    /**
     * Components array
     *
     * @var array
     */
    public $components = [
        'Flash',
        'RequestHandler',
    ];

    /**
     * Index method.
     *
     * @return void
     */
    public function index()
    {
        $this->Flash->error('An error message');
        $this->response->cookie(
            [
            'name' => 'remember_me',
            'value' => 1,
            ]
        );
        $this->set('test', 'value');
    }
}
