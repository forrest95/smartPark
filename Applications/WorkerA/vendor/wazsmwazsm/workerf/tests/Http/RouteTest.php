<?php
namespace WorkerF\Tests\Http;

use PHPUnit_Framework_TestCase;
use WorkerF\Http\Route;
use WorkerF\Http\Requests;
use WorkerF\Http\Response;
use WorkerF\Config;
use WorkerF\IOCContainer;
use WorkerF\Http\MiddlewareInterface;

class RouteFake extends Route
{
    public static function getMapTree()
    {
        return self::$_map_tree;
    }

    public static function getMiddlewareMapTree()
    {
        return self::$_middleware_map_tree;
    }

    public static function getVariableMapTree()
    {
        return self::$_variable_map_tree;
    }

    public static function getVariableRouteCache()
    {
        return self::$_variable_route_cache;
    }

    public static function setVariableRouteCache($arr)
    {
        self::$_variable_route_cache = $arr;
    }

    public static function getVariableRouteCacheIndex()
    {
        return self::$_variable_route_cache_index;
    }

    public static function setVariableRouteCacheIndex($arr)
    {
        self::$_variable_route_cache_index = $arr;
    }

    public static function getVariableRouteCacheLimit()
    {
        return self::$_variable_route_cache_limit;
    }

    public static function variableRouteCacheControl($value)
    {
        self::_variableRouteCacheControl($value);
    }

    public static function getVariableReplacement()
    {
        return self::$_variable_replacement;
    }

    public static function setFilter($prefix, $namespace, $middleware)
    {
        self::$_filter['prefix']     = $prefix;
        self::$_filter['namespace']  = $namespace;
        self::$_filter['middleware'] = $middleware;
    }

    public static function cleanFilter()
    {
        self::$_filter['prefix']     = '';
        self::$_filter['namespace']  = '';
        self::$_filter['middleware'] = [];
    }

    public static function setMapTree($method, $path, $content)
    {
        return self::_setMapTree($method, $path, $content);
    }  

    public static function cleanMapTree()
    {
        self::$_map_tree = [];
        self::$_middleware_map_tree = [];
        self::$_variable_map_tree = [];
        self::$_variable_route_cache = [];
    }

    public static function pathParse($path)
    {
        return self::_pathParse($path);
    }

    public static function namespaceParse($namespace)
    {
        return self::_namespaceParse($namespace);
    }

    public static function getRedirectUrl($path, $param)
    {
        return self::_getRedirectUrl($path, $param);
    }

    public static function isVariableRoute($path)
    {
        return self::_isVariableRoute($path);
    }

    public static function variablePathReplace($path)
    {
        return self::_variablePathReplace($path);
    }

    public static function variablePathParse($path, $method)
    {
        return self::_variablePathParse($path, $method);
    }  

    public static function runDispatch(Requests $request, $callback, $middleware_symbols, $params = [])
    {
        return self::_runDispatch($request, $callback, $middleware_symbols, $params);
    }

    public static function checkMiddleware(Requests $request, $middleware_symbols)
    {
        return self::_checkMiddleware($request, $middleware_symbols);
    }
}

class Fuck
{
    public function bar()
    {
        return 'hello bar!';
    }

    public function getRequest(Requests $request)
    {
        return $request->request();
    }

    public function post($id)
    {
        return $id;
    }

    public function post1($id, $name)
    {
        return "Name: $name, ID: $id";
    }

    public function post2(Requests $request, $id, $name)
    {
        return "Name: $name, ID: $id, query_str: ".$request->queryString();
    }
}

class M3 implements MiddlewareInterface
{
    public function handle(Requests $request)
    {
        return $request;
    }
}

class M4 implements MiddlewareInterface
{
    public function handle(Requests $request)
    {
        return function() {
            return 'stop at m4!';
        };
    }
}


class RouteTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // clean map tree
        RouteFake::cleanMapTree();
        // clean filter
        RouteFake::cleanFilter();
        // init global variables
        $GLOBALS['HTTP_RAW_POST_DATA'] = '{"a":"test"}';
        $_REQUEST = ['foo' => 'bar', 'foz' => 'baz'];
        $_SERVER  = [
          'REQUEST_URI'    => 'http://test.com/pre/test?foo=bar',
          'REQUEST_METHOD' => 'GET',
        ];
        Config::set('middleware.route', []);
        // set singleton
        IOCContainer::register(M3::class);
        IOCContainer::register(M4::class);
    }

    public function testUriParse()
    {
        $result = RouteFake::pathParse('usr//local///bin');

        $this->assertEquals('/usr/local/bin', $result);

        $result = RouteFake::pathParse('/');

        $this->assertEquals('/', $result);
    }

    public function testNamespaceParse()
    {
        $result = RouteFake::namespaceParse('\\a\\\\a\\\\\\g\d');

        $this->assertEquals("\\a\\a\\g\\d", $result);
    }

    public function testIsVariableRoute()
    {
        $path = '/a/b';
        $this->assertFalse(RouteFake::isVariableRoute($path));

        $path = '/post/{id}';
        $this->assertTrue(RouteFake::isVariableRoute($path));

        $path = '/a/{id}/b/{s_id}';
        $this->assertTrue(RouteFake::isVariableRoute($path));
    }

    public function testVariablePathReplace()
    {
        $path = '/post/{id}';
        $expect = '@^/post/'.RouteFake::getVariableReplacement().'$@';
        $this->assertEquals($expect, RouteFake::variablePathReplace($path));

        $path = '/post/{id}/name/{name}';
        $expect = '@^/post/'.RouteFake::getVariableReplacement().'/name/'.RouteFake::getVariableReplacement().'$@';
        $this->assertEquals($expect, RouteFake::variablePathReplace($path));
    }

    public function testSetMapTree()
    {
        // route map tree
        RouteFake::setMapTree('GET', '/test', 'TestController@test');
        $map = RouteFake::getMapTree();
        $this->assertEquals('\TestController@test', $map['/test']['GET']);
        RouteFake::setMapTree('GET', '/a', function() {
            return 'a';
        });
        $map = RouteFake::getMapTree();

        $this->assertEquals('a', $map['/a']['GET']());

        // route map tree with namespace
        RouteFake::setFilter('', '\\Test\\', []);  
        RouteFake::setMapTree('GET', '/cc', 'TestController@test');
        $map = RouteFake::getMapTree();

        $this->assertEquals('\Test\TestController@test', $map['/cc']['GET']);

        // middleware map tree
        RouteFake::setFilter('', '', ['auth']);    
        RouteFake::setMapTree('GET', '/bb', 'TestController@test');
        $middleware_map = RouteFake::getMiddlewareMapTree();

        $this->assertEquals(['auth'], $middleware_map['/bb']['GET']);

        // variable route map tree
        RouteFake::setFilter('', '', []);  
        RouteFake::setMapTree('GET', '/post/{id}', 'TestController@test');
        $variable_map = RouteFake::getVariableMapTree();
        $path = RouteFake::variablePathReplace('/post/{id}');

        $this->assertEquals('\TestController@test', $variable_map[$path]['GET']);
    }

    public function testVariablePathParse()
    {
        RouteFake::setFilter('/pre', '\\Test\\', ['auth', 'jwt']);  
        RouteFake::setMapTree('GET', '/post/{id}', 'TestController@test');
        $path_info = RouteFake::variablePathParse('/pre/post/12', 'GET');

        $this->assertEquals('\Test\TestController@test', $path_info['callback']);
        $this->assertEquals([12], $path_info['params']);
        $this->assertEquals(['auth', 'jwt'], $path_info['middleware']);

        // more params
        RouteFake::setFilter('', '\\App\\', ['auth']);  
        RouteFake::setMapTree('POST', '/a/{id}/b/{name}/{a_id}', 'TestController@test');
        $path_info = RouteFake::variablePathParse('/a/25/b/jack/3', 'POST');

        $this->assertEquals('\App\TestController@test', $path_info['callback']);
        $this->assertEquals([25, 'jack', 3], $path_info['params']);
        $this->assertEquals(['auth'], $path_info['middleware']);
    }

    public function testSetRoute()
    {
        // GET, callback
        RouteFake::get('/a', function() {
            return 'a';
        });
        $map = RouteFake::getMapTree();

        $this->assertEquals('a', $map['/a']['GET']());

        // GET, string
        RouteFake::get('/b', 'Test\Controller@get');
        $map = RouteFake::getMapTree();

        $this->assertEquals('\Test\Controller@get', $map['/b']['GET']);

        // POST
        RouteFake::post('/c', function() {
            return 'c';
        });
        $map = RouteFake::getMapTree();

        $this->assertEquals('c', $map['/c']['POST']());

        // PUT (same as DELETE\PATCH)
        RouteFake::put('/d/e', 'Test\Controller@get');
        $map = RouteFake::getMapTree();

        $this->assertEquals('\Test\Controller@get', $map['/d/e']['PUT']);
    }

    public function testSetVariableRoute()
    {
        // callback
        RouteFake::get('/post/{id}', function() {
            return 'a';
        });
        $map = RouteFake::getVariableMapTree();
        $path = RouteFake::variablePathReplace('/post/{id}');

        $this->assertEquals('a', $map[$path]['GET']());

        // string
        RouteFake::get('/goods/{id}/name/{name}', 'Test\Controller@get');
        $map = RouteFake::getVariableMapTree();
        $path = RouteFake::variablePathReplace('/goods/{id}/name/{name}');

        $this->assertEquals('\Test\Controller@get', $map[$path]['GET']);

    }

    public function testGroup()
    {       
        RouteFake::group(['prefix' => '/pre', 'namespace' => 'App\Controller', 'middleware' => 'auth'], function() {
            RouteFake::get('control/', 'TestController@test');
            RouteFake::post('call1/', function() {
                return 'hello1';
            });
            RouteFake::get('call2/', function() {
                return 'hello2';
            });
        });

        $map = RouteFake::getMapTree();
        $middleware_map = RouteFake::getMiddlewareMapTree();

        $this->assertEquals('\App\Controller\TestController@test', $map['/pre/control']['GET']);
        $this->assertEquals('hello1', $map['/pre/call1']['POST']());
        $this->assertEquals('hello2', $map['/pre/call2']['GET']());

        $this->assertEquals(['auth'], $middleware_map['/pre/control']['GET']);
        $this->assertEquals(['auth'], $middleware_map['/pre/call1']['POST']);
        $this->assertEquals(['auth'], $middleware_map['/pre/call2']['GET']);

        // group nesting
        RouteFake::group(['prefix' => '/g1', 'namespace' => 'App', 'middleware' => 'auth'], function() {
            RouteFake::group(['prefix' => '/g2', 'namespace' => 'Controller', 'middleware' => 'jwt'], function() {
                RouteFake::get('test', function() {
                    return 'g1 g2 test success';
                });
                RouteFake::get('con', "TestController@test");
            });

            RouteFake::get('test', function() {
                return 'g1 test success';
            });
        });

        $map = RouteFake::getMapTree();
        $middleware_map = RouteFake::getMiddlewareMapTree();

        $this->assertEquals('g1 g2 test success', $map['/g1/g2/test']['GET']());
        $this->assertEquals('\App\Controller\TestController@test', $map['/g1/g2/con']['GET']);
        $this->assertEquals('g1 test success', $map['/g1/test']['GET']());

        $this->assertEquals(['auth'], $middleware_map['/g1/test']['GET']);
        $this->assertEquals(['auth', 'jwt'], $middleware_map['/g1/g2/test']['GET']);
        $this->assertEquals(['auth', 'jwt'], $middleware_map['/g1/g2/con']['GET']);

        // with variable route
        RouteFake::group(['prefix' => '/f1', 'namespace' => 'App', 'middleware' => 'auth'], function() {
            RouteFake::group(['prefix' => '/f2', 'namespace' => 'Controller', 'middleware' => 'jwt'], function() {
                RouteFake::get('test', function() {
                    return 'f1 f2 test success';
                });
                RouteFake::get('a/{id}/b/{s_id}', "TestController@test");
            });

            RouteFake::get('post/{id}', function() {
                return 'post';
            });
        });

        $map = RouteFake::getMapTree();
        $variable_map = RouteFake::getVariableMapTree();
        $middleware_map = RouteFake::getMiddlewareMapTree();
        $ab_path = RouteFake::variablePathReplace('/f1/f2/a/{id}/b/{s_id}');
        $post_path = RouteFake::variablePathReplace('/f1/post/{id}');

        $this->assertEquals('f1 f2 test success', $map['/f1/f2/test']['GET']());
        $this->assertEquals('\App\Controller\TestController@test', $variable_map[$ab_path]['GET']);
        $this->assertEquals('post', $variable_map[$post_path]['GET']());

        $this->assertEquals(['auth'], $middleware_map[$post_path]['GET']);
        $this->assertEquals(['auth', 'jwt'], $middleware_map['/f1/f2/test']['GET']);
        $this->assertEquals(['auth', 'jwt'], $middleware_map[$ab_path]['GET']);

    }

    public function testCheckMiddleware()
    {
        // middleware check passed
        Config::set('middleware.route', ['auth' => M3::class]);
        $request = new Requests();
        $middleware_symbols = ['auth'];
        $result = RouteFake::checkMiddleware($request, $middleware_symbols);

        $this->assertEquals($request, $result);
        
        // middleware check not passed
        Config::set('middleware.route', ['auth' => M4::class]);
        $request = new Requests();
        $middleware_symbols = ['auth'];
        $result = RouteFake::checkMiddleware($request, $middleware_symbols);

        $this->assertEquals('stop at m4!', $result);
    }

    public function testRunDispatch()
    {
        // callback
        $request = new Requests();
        $callback = function() {
            return 'a';
        };
        $middleware_symbols = [];
        $params = [];
        $result = RouteFake::runDispatch($request, $callback, $middleware_symbols, $params);

        $this->assertEquals('a', $result);

        // class@method
        $request = new Requests();
        $callback = 'WorkerF\Tests\Http\Fuck@bar';
        $middleware_symbols = [];
        $params = [];
        $result = RouteFake::runDispatch($request, $callback, $middleware_symbols, $params);

        $this->assertEquals('hello bar!', $result);

        // middleware 
        Config::set('middleware.route', ['auth' => M4::class]);
        $request = new Requests();
        $callback = 'WorkerF\Tests\Http\Fuck@bar';
        $middleware_symbols = ['auth'];
        $params = [];
        $result = RouteFake::runDispatch($request, $callback, $middleware_symbols, $params);

        $this->assertEquals('stop at m4!', $result);

        // callback with params
        $request = new Requests();
        $callback = function($id) {
            return $id;
        };
        $middleware_symbols = [];
        $params = [12];
        $result = RouteFake::runDispatch($request, $callback, $middleware_symbols, $params);

        $this->assertEquals(12, $result);

        // class@method with params
        $request = new Requests();
        $callback = 'WorkerF\Tests\Http\Fuck@post1';
        $middleware_symbols = [];
        $params = [233, 'bili'];
        $result = RouteFake::runDispatch($request, $callback, $middleware_symbols, $params);

        $this->assertEquals('Name: bili, ID: 233', $result);
    }

    public function testDispatch()
    {
        // class@method
        $request = new Requests();

        RouteFake::get('/pre/test', 'WorkerF\Tests\Http\Fuck@bar');
        $result = RouteFake::dispatch($request);

        $this->assertEquals('hello bar!', $result);

        // class@method DI
        RouteFake::get('/pre/test', 'WorkerF\Tests\Http\Fuck@getRequest');
        $result = RouteFake::dispatch($request);

        $this->assertEquals((object) $_REQUEST, $result);

        // callback
        RouteFake::get('/pre/test', function() {
            return 'hello';
        });
        $result = RouteFake::dispatch($request);

        $this->assertEquals('hello', $result);

        // with middleware
        // middleware check passed
        Config::set('middleware.route', ['auth' => M3::class]);
        $request = new Requests();
        RouteFake::group(['prefix' => '/pre', 'middleware' => 'auth'], function() {    
            RouteFake::get('/test', 'WorkerF\Tests\Http\Fuck@bar');
        });    

        $result = RouteFake::dispatch($request);

        $this->assertEquals('hello bar!', $result);

        // middleware check not passed
        Config::set('middleware.route', ['auth' => M4::class]);
        $request = new Requests();
        RouteFake::group(['prefix' => '/pre', 'middleware' => 'auth'], function() {    
            RouteFake::get('/test', 'WorkerF\Tests\Http\Fuck@bar');
        });    

        $result = RouteFake::dispatch($request);

        $this->assertEquals('stop at m4!', $result);
    }

    public function testVariableDispatch()
    {
        // class@method 
        $_SERVER  = [
          'REQUEST_URI'    => 'http://test.com/post/8',
          'REQUEST_METHOD' => 'GET',
        ];
        
        $request = new Requests();

        RouteFake::get('/post/{id}', 'WorkerF\Tests\Http\Fuck@post');
        $result = RouteFake::dispatch($request);

        $this->assertEquals(8, $result);
        // class@method DI
        $_SERVER  = [
            'REQUEST_URI'    => 'http://test.com/post2/20/name/mike',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'a=2&b3',
          ];
        $request = new Requests();

        RouteFake::get('/post2/{id}/name/{name}', 'WorkerF\Tests\Http\Fuck@post2');
        $result = RouteFake::dispatch($request);

        $this->assertEquals('Name: mike, ID: 20, query_str: a=2&b3', $result);
        
        // callback
        $_SERVER  = [
            'REQUEST_URI'    => 'http://test.com/a/5/2',
            'REQUEST_METHOD' => 'GET',
          ];
        $request = new Requests();
        RouteFake::get('/a/{id}/{pid}', function($id, $pid) {
            return [$id, $pid];
        });
        $result = RouteFake::dispatch($request);

        $this->assertEquals([5, 2], $result);

        // with middleware
        // middleware check passed
        Config::set('middleware.route', ['auth' => M3::class]);
        $_SERVER  = [
            'REQUEST_URI'    => 'http://test.com/pre/post/2',
            'REQUEST_METHOD' => 'GET',
          ];
        $request = new Requests();
        RouteFake::group(['prefix' => '/pre', 'middleware' => 'auth'], function() {    
            RouteFake::get('/post/{id}', 'WorkerF\Tests\Http\Fuck@post');
        });    

        $result = RouteFake::dispatch($request);

        $this->assertEquals('2', $result);

        // middleware check not passed
        Config::set('middleware.route', ['auth' => M4::class]);
        $_SERVER  = [
            'REQUEST_URI'    => 'http://test.com/pre/post/2',
            'REQUEST_METHOD' => 'GET',
          ];
        $request = new Requests();
        RouteFake::group(['prefix' => '/pre', 'middleware' => 'auth'], function() {    
            RouteFake::get('/post/{id}', 'WorkerF\Tests\Http\Fuck@post');
        });    

        $result = RouteFake::dispatch($request);

        $this->assertEquals('stop at m4!', $result);
    }

    public function testVariableCache()
    {
        $variableCache = RouteFake::getVariableRouteCache();
        $path = '/post/8';
        $this->assertFalse(array_key_exists($path, $variableCache));
        
        $_SERVER  = [
            'REQUEST_URI'    => 'http://test.com/post/8',
            'REQUEST_METHOD' => 'GET',
          ];
        Config::set('middleware.route', ['auth' => M3::class]);
        $request = new Requests();
        RouteFake::group(['middleware' => 'auth'], function() {
            RouteFake::get('/post/{id}', 'WorkerF\Tests\Http\Fuck@post');
        });  
        
        $result = RouteFake::dispatch($request);
        $this->assertEquals(8, $result);

        $variableCache = RouteFake::getVariableRouteCache();
        $path = '/post/8';

        $this->assertTrue(array_key_exists($path, $variableCache));
        $this->assertEquals('\WorkerF\Tests\Http\Fuck@post', $variableCache[$path]['GET']['callback']);
        $this->assertEquals([8], $variableCache[$path]['GET']['params']);
        $this->assertEquals(['auth'], $variableCache[$path]['GET']['middleware']);

        // dispatch when variable already cached
        $result = RouteFake::dispatch($request);
        $this->assertEquals(8, $result);
    }

    public function testVariableRouteCacheControl()
    {
        // set cache limit
        RouteFake::setVariableRouteCacheLimit(10);

        /* data not out of range */

        $route_cache_index = [9, 1, 5, 6, 7, 2];
        RouteFake::setVariableRouteCacheIndex($route_cache_index);
        RouteFake::setVariableRouteCache([9 => 'a', 7 => 'c', 1 => 'b']);

        // value add
        RouteFake::variableRouteCacheControl(15); 
        $route_cache_index = RouteFake::getVariableRouteCacheIndex();
        $this->assertEquals([9, 1, 5, 6, 7, 2, 15], $route_cache_index);

        // value hit
        RouteFake::variableRouteCacheControl(1);
        $route_cache_index = RouteFake::getVariableRouteCacheIndex();
        $this->assertEquals([9, 5, 6, 7, 2, 15, 1], $route_cache_index);
        RouteFake::variableRouteCacheControl(7);
        $route_cache_index = RouteFake::getVariableRouteCacheIndex();
        $this->assertEquals([9, 5, 6, 2, 15, 1, 7], $route_cache_index);
        $route_cache = RouteFake::getVariableRouteCache();
        $this->assertEquals([9 => 'a', 7 => 'c', 1 => 'b'], $route_cache);

        /* data out of range */

        $route_cache_index = [9, 1, 5, 6, 7, 2, 11, 3, 15, 4];
        RouteFake::setVariableRouteCacheIndex($route_cache_index);
        RouteFake::setVariableRouteCache([9 => 'a', 7 => 'c', 1 => 'b']);

        // value add
        RouteFake::variableRouteCacheControl(18); 
        $route_cache_index = RouteFake::getVariableRouteCacheIndex();
        $route_cache = RouteFake::getVariableRouteCache();
        $this->assertEquals([1, 5, 6, 7, 2, 11, 3, 15, 4, 18], $route_cache_index);
        $this->assertEquals([7 => 'c', 1 => 'b'], $route_cache);
        RouteFake::variableRouteCacheControl(16); 
        $route_cache_index = RouteFake::getVariableRouteCacheIndex();
        $route_cache = RouteFake::getVariableRouteCache();
        $this->assertEquals([5, 6, 7, 2, 11, 3, 15, 4, 18, 16], $route_cache_index);
        $this->assertEquals([7 => 'c'], $route_cache);

        // value hit
        RouteFake::variableRouteCacheControl(2);
        $route_cache_index = RouteFake::getVariableRouteCacheIndex();
        $this->assertEquals([5, 6, 7, 11, 3, 15, 4, 18, 16, 2], $route_cache_index);
        $this->assertEquals([7 => 'c'], $route_cache);
    }

    public function testSetVariableRouteCacheLimit()
    {
        RouteFake::setVariableRouteCacheLimit(552);

        $this->assertEquals(552, RouteFake::getVariableRouteCacheLimit());
    }

    /**
    * @expectedException \InvalidArgumentException
    */
    public function testSetVariableRouteCacheLimitException()
    {
        RouteFake::setVariableRouteCacheLimit(-1);
    }

    /**
    * @expectedException \InvalidArgumentException
    */
    public function testSetVariableRouteCacheLimitException2()
    {
        RouteFake::setVariableRouteCacheLimit(['a' => 1, 'b' => 2]);
    }

    public function testGetRedirectUrl()
    {
        Config::set('app.base_url', 'http://test.com/');
        $url = RouteFake::getRedirectUrl('/pre/test', ['foo' => 1, 'bar' => 2]);

        $this->assertEquals('http://test.com/pre/test?foo=1&bar=2', $url);
    }

    public function testGetRedirect()
    {
        Config::set('app.base_url', 'http://test.com/');
        $redirectClosure = RouteFake::redirect('/pre/test', ['foo' => 1, 'bar' => 2]);
        $result = call_user_func($redirectClosure);
        
        $this->assertEquals('HTTP/1.1 302 Found', Response::getHeader('Http-Code'));
        $this->assertEquals('Location: http://test.com/pre/test?foo=1&bar=2', Response::getHeader('Location'));
        $this->assertEquals('redirect', $result);
    }

    /**
    * @expectedException \LogicException
    */
    public function testDispatchRouteNotSetException()
    {
        $request = new Requests();

        RouteFake::dispatch($request);
    }

    /**
    * @expectedException \LogicException
    */
    public function testDispatchMethodNotMatchException()
    {
        $request = new Requests();

        RouteFake::get('/pre/test', 'ssssss');
        RouteFake::dispatch($request);
    }

    /**
    * @expectedException \BadMethodCallException
    */
    public function testDispatchMethodNotFoundException()
    {
        $request = new Requests();

        RouteFake::get('/pre/test', 'Foz@baz');
        RouteFake::dispatch($request);
    }

    /**
    * @expectedException \InvalidArgumentException
    */
    public function testCheckMiddlewareException()
    {
        // middleware check passed
        Config::set('middleware.route', ['auth' => M3::class]);
        $request = new Requests();

        $middleware_symbols = ['some'];
        $result = RouteFake::checkMiddleware($request, $middleware_symbols);
    }

    /**
    * @expectedException \InvalidArgumentException
    */
    public function testDispatchCheckMiddlewareException()
    {
        // middleware check passed
        Config::set('middleware.route', ['auth' => M3::class]);
        $request = new Requests();
        RouteFake::group(['prefix' => '/pre', 'middleware' => 'some'], function() {    
            RouteFake::get('/test', 'WorkerF\Tests\Http\Fuck@bar');
        });    

        $result = RouteFake::dispatch($request);
    }
}
