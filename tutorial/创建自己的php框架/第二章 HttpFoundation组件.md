[原文地址](https://symfony.com/doc/current/create_framework/http_foundation.html)

在深入框架的创建进程之前，先后退一步，先来看看为什么我们宁愿使用框架也不愿让老旧的PHP程序保持原样。
为什么即使是一小段简单的PHP代码也要使用框架。以及为什么在Symfony组件的基础上创建框架比从零开始创建要好。

> 我们不会讨论在多人参与的大型项目中使用框架的好处，网上已经有很多很好的关于此主题的资源。

尽管我们在第一章写的那个应用的代码已经足够简单了，但是还是存在问题：
```php
// framework/index.php
$name = $_GET['name'];

printf('Hello %s', $name);
```
首先，如果在URL查询字符串中没有定义`name`这个参数，那么将会出现PHP警告。
修正代码：
```php
// framework/index.php
$name = $_GET['name'] ?? 'World';

printf('Hello %s', $name);
```
其次，这个应用有安全漏洞。你能想象吗，即使是如此简单的一段PHP代码，也存在一个网络安全漏洞，XSS（跨站脚本攻击）。
下面是一个更安全的版本：
```php
// framework/index.php
$name = $_GET['name'] ?? 'World';
header('Content-Type: text/html; charset=utf-8');
printf('Hello %s', htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
```
> 这或许就是采用[Twig](https://twig.symfony.com/)这类默认开启自动转义功能的模板引擎的优势所在（当需要显式转义时，配合简洁的e过滤器使用也不会过于繁琐）

正如你自己所看到的，如果我们想要避免出现PHP警告/通知并使代码更安全，那么我们最初编写的简单代码就不再那么简单了。

除了安全性，代码还需要做复杂的测试。尽管这段代码没什么需要太多测试的东西，但是我觉得，给简单的PHP代码写单元测试不应该感到不自然和难为情。
下面是上面代码的一个实验性PHPUnit单元测试：
```php
// framework/test.php
use PHPUnit\Framework\TestCase;
class IndexTest extends TestCase 
{
    public function testHello(): void
    {
        $_GET['name'] = 'Fabien';

        ob_start();
        include 'index.php';
        $content = ob_get_clean();

        $this->assertEquals('Hello Fabien', $content);
    }
}
```
> 如果我们的应用稍微变得更大的话，我们可能会发现更多的问题。如果你对此感兴趣的话可以阅读[Symfony versus Flat PHP](https://symfony.com/doc/current/introduction/from_flat_php_to_symfony.html)章节。

多说一句，如果你不考虑安全性和单元测试，那么你可以继续使用老的方式写代码，也可以不使用框架。那么你就没必要再看这个教程了，继续保持你以前的习惯就好。

> 使用框架给你提供的不仅仅是安全性和单元测试的能力，你要知道使用框架可以让你更快地写出更好的代码。

## 使用HttpFoundation组件面向对象
web应用通过HTTP协议交互，所以我们的框架的基本规则要符合HTTP协议标准。

HTTP协议规定了客户端如何与服务端进行交互。会话中包含消息，请求和响应。客户端向服务端发送一个请求，服务端基于该请求给客户端返回一个响应。

在PHP中，请求内容可以通过全局变量获取（`$_GET`,`$_POST`,`$_FILE`,`$_COOKIE`,`$_SESSION`...），响应通过函数生成（`echo`,`header`,`setcookie`...）。

写出好代码的第一步是使用面向对象思想。
Symfony框架的HttpFoundation组件的主要目的就是使用一个面向对象层替代PHP原来的全局变量和函数。
使用下面的命令在项目中添加HttpFoundation组件：
```
composer require symfony/http-foundation
```
运行上面命令会自动下载HttpFoundation组件到`vendor`目录下。
`composer.json`和`composer.lock`文件也会相应的更新。

>类的自动加载：当使用composer安装依赖时，composer会自动生成`vender/autoload.php`文件，允许任何类自动加载（后面安装的新依赖也通过这个文件自动加载）。如果没有自动加载，在使用一个类之前，你需要先手动引入这个类然后才能使用。多亏了[PSR-40](https://www.php-fig.org/psr/psr-4/)，composer和PHP帮我们完成了这项工作。

现在，我们使用`Request`类和`Response`类重写我们的应用：
```php
// framework/index.php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$request = Request::createFromGlobals();

$name = $request->query-get('name','World');

$response = new Response(sprintf('Hello %s',htmlspecialchars($name,ENT_QUOTES,'UTF-8')));

$response->send();
```
`createFromGlobals()`方法基于PHP当前的全局变量生成了一个`Request`对象。

`send()`方法发送`Response`对象给客户端。

> 在调`send()`方法之前，我们应该先调`prepare()`方法（`$response->prepare($request)`），确保响应符合HTTP协议。比如，如果我们使用`HEAD`方法调用页面，响应内容会被移除。

重写后的代码与之前的代码的主要不同是，你可以完全掌控HTTP消息：
你可以创建任何你想要的请求，你也可以在你认为任何合适的时机发送响应。
> 重写的代码中我们没有明确指定头部的`Content-Type`，默认情况下响应体的`Content-Type`是`UTF-8`。

有了Request类，所有的请求信息都唾手可得，这要归功于其简洁好用的API：
```php
// the URI being requested (e.g. /about) minus any query parameters
$request->getPathInfo();

// retrieve GET and POST variables respectively
$request->query->get('foo');
$request->request->get('bar', 'default value if bar does not exist');

// retrieve SERVER variables
$request->server->get('HTTP_HOST');

// retrieves an instance of UploadedFile identified by foo
$request->files->get('foo');

// retrieve a COOKIE value
$request->cookies->get('PHPSESSID');

// retrieve an HTTP request header, with normalized, lowercase keys
$request->headers->get('host');
$request->headers->get('content-type');

$request->getMethod(); // GET, POST, PUT, DELETE, HEAD
$request->getLanguages(); // an array of languages the client accepts

```
你也可以模拟一个请求。
```php
$requst = Request::create('/index.php?name=Fabien');
```
使用`Response`类，你可以对响应稍作调整：
```php
$response = new Response();

$response->setContent('Hello world');
$response->setStatusCode(200);
$response->headers->set('Content-Type','text/html');

// configure the HTTP cache headers
$response->setMaxAge(10);
```
> 为了调试响应，将响应转换为字符串。这样将会返回响应的HTTP描述（包含响应头和响应内容）。

最后但同样重要的一点，上面用到的类，和Symfony框架中的其他类一样，都由一个独立的公司做过安全性审查。作为一个开源项目，也意味着，来自世界各地的其他开发者都读过这些代码，并且已经修复了潜在的安全性问题。你最近一次对你自制的框架进行专业的安全审查是什么时候？

像获取客户端IP这样简单的事情都可能是不安全的：
```php
if($myIp === $_SERVER['REMOTE_ADDR']) {
    // the client is a known one, so give it some more privilege
}
```
上面的代码效果很好，但是当你在线上服务器之前添加一个反向代理的话，上面的代码可能就不那么好用了。此时，你需要调整你的代码以确保其同时也能在你的开发服务器上（通常没有反向代理）运行。
```php
if($myIp === $_SERVER['HTTP_X_FORWARDED_FOR'] || $myIp === $_SERVER['REMOTE_ADDR']) {
    // the client is a known one, so give it some more privilege
}
```
还是获取客户端IP的例子，如果一开始就使用`Request::getClientIp()`方法的话会方便很多，因为该方法已经考虑到使用代理的情况了：
```php
$request = Request::createFromGlobals();

if($myIp === $request->getClientIp()) {
    // the client is a known one, so give it some more privilege
}
```
使用`Request::getClientIp()`方法还有一个另外的好处：该方法更安全。
因为`$_server['HTTP_X_FORWARDED_FOR']`的值在没有代理的情况下在客户端可以被修改。
所以如果在没有代理的生产环境使用这个值，很容易导致你的系统被滥用。
通过调用`setTrustedProxies()`明确地信任你的反向代理，再使用`getClientIp()`方法，就不会有上述问题。
```php
Request::setTrustedProxies(['10.0.0.1'],Request::HEADER_X_FORWARDED_FOR);

if($myIp === $request->getClientIp()) {
    // the client is a known one, so give it some more privilege
}
```
因此，`getClientIp()`方法在所有环境中都能安全运行。你可以在任何项目中使用这个方法，无论环境配置什么样，它都可以正确地和安全地运行。这也是使用框架的原因。

如果你从头开始编写一个框架，那么你不得不独自考虑所有这些使用情况。既如此，为什么不使用别人已经写好的呢？
> 如果你想了解更多关于 HttpFoundation 组件的信息，你可以看看`Symfony\Component\HttpFoundation`的API和相关文档。

不管你信不信，现在我们已经有了属于我们自己的一个框架。
仅仅使用 Symfony 的 HttpFoundation 组件就可以让我们写出更好的更健壮的代码。也可以让我们写得更快，因为许多日常的问题，它已经帮我们解决好了。

事实上，像Drupal这种项目已经采用 HttpFoundation组件。他们能用你为什么不可以用呢？不要重复造轮子。

我差点忘了说另外一个好处：使用 HttpFoundation组件是与其他所有使用该组件的框架和应用之间建立互操作性的开始（像 Symfony，Drupal8，phpBB 3，Laravel和ezPublish等）。

