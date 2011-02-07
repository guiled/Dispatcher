<?php

/**
 * Hoa Framework
 *
 *
 * @license
 *
 * GNU General Public License
 *
 * This file is part of Hoa Open Accessibility.
 * Copyright (c) 2007, 2010 Ivan ENDERLIN. All rights reserved.
 *
 * HOA Open Accessibility is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HOA Open Accessibility is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HOA Open Accessibility; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace {

from('Hoa')

/**
 * \Hoa\Controller\Exception
 */
-> import('Controller.Exception')

/**
 * \Hoa\Controller\Application
 */
-> import('Controller.Application');

}

namespace Hoa\Controller\Dispatcher {

/**
 * Class \Hoa\Controller\Dispatcher.
 *
 * .
 *
 * @author     Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright  Copyright (c) 2007, 2010 Ivan ENDERLIN.
 * @license    http://gnu.org/licenses/gpl.txt GNU GPL
 */

abstract class Dispatcher implements \Hoa\Core\Parameterizable {

    /**
     * The \Hoa\Controller\Dispatcher parameters.
     *
     * @var \Hoa\Core\Parameter object
     */
    protected $_parameters  = null;

    /**
     * Current view.
     *
     * @var \Hoa\View\Viewable object
     */
    protected $_currentView = null;



    /**
     * Build a new dispatcher.
     *
     * @access  public
     * @param   array   $parameters    Parameters.
     * @return  void
     */
    public function __construct ( Array $parameters = array() ) {

        $this->_parameters = new \Hoa\Core\Parameter(
            $this,
            array(
                'controller' => 'main',
                'action'     => 'main',
                'method'     => null
            ),
            array(
                'synchronous.file'        => 'hoa://Application/Controller/(:controller:U:).php',
                'synchronous.controller'  => '(:controller:U:)Controller',
                'synchronous.action'      => '(:action:U:)Action',

                'asynchronous.file'       => '(:%synchronous.file:)',
                'asynchronous.controller' => '(:%synchronous.controller:)',
                'asynchronous.action'     => '(:%synchronous.action:)Async'
            )
        );
        $this->setParameters($parameters);

        return;
    }

    /**
     * Set many parameters to a class.
     *
     * @access  public
     * @param   array   $in    Parameters to set.
     * @return  void
     * @throw   \Hoa\Core\Exception
     */
    public function setParameters ( Array $in ) {

        return $this->_parameters->setParameters($this, $in);
    }

    /**
     * Get many parameters from a class.
     *
     * @access  public
     * @return  array
     * @throw   \Hoa\Core\Exception
     */
    public function getParameters ( ) {

        return $this->_parameters->getParameters($this);
    }

    /**
     * Set a parameter to a class.
     *
     * @access  public
     * @param   string  $key      Key.
     * @param   mixed   $value    Value.
     * @return  mixed
     * @throw   \Hoa\Core\Exception
     */
    public function setParameter ( $key, $value ) {

        return $this->_parameters->setParameter($this, $key, $value);
    }

    /**
     * Get a parameter from a class.
     *
     * @access  public
     * @param   string  $key    Key.
     * @return  mixed
     * @throw   \Hoa\Core\Exception
     */
    public function getParameter ( $key ) {

        return $this->_parameters->getParameter($this, $key);
    }

    /**
     * Get a formatted parameter from a class (i.e. zFormat with keywords and
     * other parameters).
     *
     * @access  public
     * @param   string  $key    Key.
     * @return  mixed
     * @throw   \Hoa\Core\Exception
     */
    public function getFormattedParameter ( $key ) {

        return $this->_parameters->getFormattedParameter($this, $key);
    }

    /**
     * Dispatch a router rule to a controller and an action (could be a class, a
     * stream, a closure, a function etc.).
     *
     * @access  public
     * @param   \Hoa\Controller\Router  $router    Router.
     * @param   \Hoa\View\Viewable      $view      View.
     * @return  mixed
     * @throw   \Hoa\Controller\Exception
     */
    public function dispatch ( \Hoa\Controller\Router $router,
                               \Hoa\View\Viewable     $view = null ) {

        $rule     = $router->getTheRule();

        if(null === $rule)
            $rule = $router->route()->getTheRule();

        if(null === $view)
            $view = $this->_currentView;
        else
            $this->_currentView = $view;

        $rule[\Hoa\Controller\Router::RULE_COMPONENT]['_this']
            = new \Hoa\Controller\Application(
                $router,
                $this,
                $view
            );

        if(!isset($_SERVER['REQUEST_METHOD'])) {

            if(!isset($_SERVER['argv']))
                throw new \Hoa\Controller\Exception(
                    'Cannot identified the request method.', 0);

            $method = null;
        }
        else
            $method = strtoupper($_SERVER['REQUEST_METHOD']);

        $this->_parameters->setKeyword($this, 'method', strtolower($method));

        return $this->resolve(
            $rule[\Hoa\Controller\Router::RULE_COMPONENT],
            $rule[\Hoa\Controller\Router::RULE_PATTERN]
        );
    }

    /**
     * Resolve the dispatch call.
     *
     * @access  protected
     * @param   array      $components    All components from the router.
     * @param   string     $pattern       Pattern (kind of ID).
     * @return  mixed
     * @throw   \Hoa\Controller\Exception
     */
    abstract protected function resolve ( Array $components, $pattern );

    /**
     * Try to know if the dispatcher is called asynchronously.
     *
     * @access  public
     * @return  bool
     */
    public function isCalledAsynchronously ( ) {

        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']))
            return false;

        return 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']);
    }
}

}
