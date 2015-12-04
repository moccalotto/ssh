# SSH library.

Wrapper for the SSH2 PECL.

```php
#!/usr/bin/env php
<?php

use Moccalotto\Ssh\Auth;
use Moccalotto\Ssh\Session;
use Moccalotto\Ssh\Connect;
use Moccalotto\Ssh\Terminal;

require 'vendor/autoload.php';

$ip = '127.0.0.1';
$username = 'my_username';
$port = 22;
$pubkeyfile = '/path/to/my/key/id_rsa';
$privkeyfile = 'path/to/my/key/id_rsa.pub';
$keypass = 'my_password';

/*
| Create an SSH session
|-----------------------
| Connect to specified IP and port, and authorize via SSH key.
| You can authorize via password by calling Auth::viaPassword($username, $password)
| You can authorize via SSH agent by calling Auth::viaAgent($username)
*/
$ssh = new Session(
    Connect::to($ip, $port),
    Auth::viaKeyFile($username, $pubkeyfile, $privkeyfile, $keypass)
);

/*
| Open a SFTP channel
|---------------------
| Generate a random number, write it to a file on the remote server.
| Read the number back from the file.
*/
$sftp = $ssh->sftp();
$random = mt_rand();
$sftp->putContents('testFile.txt', $random);

if ($random == $sftp->getContents('testFile.txt')) {
    echo 'File testFile.txt was written on remote server, and the contents verified';
    echo PHP_EOL;
} else {
    echo 'File testFile.txt was written on remote server, but the contents could not be verified';
    echo PHP_EOL;
}

/*
| Create terminal settings
|--------------------------
| This step is optional.
| You do not need to call $ssh-withTerminal()
| If you don't. Default terminal settings will be used.
*/
$terminal = Terminal::create()
    ->width(80, 'chars')
    ->height(25, 'chars');

/*
| Execute a single command on the remote server
| ----------------------------------------------
| Capture its output and echo it on the local screen.
 */
echo 'Output of »echo $HOME $PWD«: ';
echo $ssh->withTerminal($terminal)->execute('echo $HOME $PWD');
echo PHP_EOL;

/*
| Send a file via SCP
| --------------------
| Simply send this file (demo.php) to the remote server.
*/
if ($ssh->sendFile(__FILE__, basename(__FILE__))) {
    printf('File: %s was sent to the remote server'.PHP_EOL, basename(__FILE__));
}

/*
| Open a shell on the remote server
|--------------------------
| Open a shell.
| Execute a few commands.
| Logout.
| Print the output from the shell.
| The shell is automatically closed when callback returns.
*/
echo $ssh->withTerminal($terminal)->shell(function ($shell) {

    $captured_output = $shell
        ->writeline('echo The home dir is: $HOME')
        ->writeline('echo the contents of $PWD is:; ls -lah')
        ->writeline('logout')
        ->wait(0.3) // give the shell time to execute the commands.
        ->readToEnd();

    return $captured_output;
});
```
