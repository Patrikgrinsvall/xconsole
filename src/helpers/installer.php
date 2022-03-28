<?php
/**
 * WIP to get swoole web and socket server as local development environment
 *
 * NOTE ! Download and install https://master.dl.sourceforge.net/project/swoole.mirror/v4.8.8/swoole-cli-v4.8.8-cygwin64.zip?viasf=1
 *
 *
 * @todo
 *      1. download swole cli and unpack here.
 *      2. run swoole-cli with clean environment variables, must not load other cygwin inaries
 *
 *
 */




$packages = [
    '_autorebase' => '001091-1',
    'alternatives' => '1.3.30c-10',
    'base-cygwin' => '3.8-1',
    'base-files' => '4.3-3',
    'bash' => '4.4.12-3',
    'bzip2' => '1.0.8-1',
    'ca-certificates' => '2021.2.52-1',
    'coreutils' => '8.26-2',
    'crypto-policies' => '20190218-1',
    'cygutils' => '1.4.17-1',
    'cygwin' => '3.3.4-2',
    'dash' => '0.5.11.5-1',
    'dejavu-fonts' => '2.37-1',
    'diffutils' => '3.8-1',
    'editrights' => '1.03-1',
    'file' => '5.41-2',
    'findutils' => '4.9.0-1',
    'gawk' => '5.1.1-1',
    'getent' => '2.18.90-4',
    'grep' => '3.7-2',
    'groff' => '1.22.4-1',
    'gzip' => '1.11-1',
    'hostname' => '3.13-1',
    'info' => '6.8-2',
    'ipc-utils' => '1.0-2',
    'less' => '590-1',
    'libargp' => '20110921-3',
    'libattr1' => '2.4.48-2',
    'libblkid1' => '2.33.1-2',
    'libbrotlicommon1' => '1.0.9-2',
    'libbrotlidec1' => '1.0.9-2',
    'libbz2_1' => '1.0.8-1',
    'libexpat1' => '2.4.1-1',
    'libfdisk1' => '2.33.1-2',
    'libffi6' => '3.2.1-2',
    'libfontconfig-common' => '2.13.1-2',
    'libfontconfig1' => '2.13.1-2',
    'libfreetype6' => '2.11.0-2',
    'libgcc1' => '11.2.0-1',
    'libgdbm6' => '1.18.1-1',
    'libgmp10' => '6.2.1-2',
    'libICE6' => '1.0.10-1',
    'libiconv2' => '1.16-2',
    'libintl8' => '0.21-1',
    'liblz4_1' => '1.7.5-1',
    'liblzma5' => '5.2.5-1',
    'libmpfr6' => '4.1.0-2',
    'libncursesw10' => '6.1-1.20190727',
    'libp11-kit0' => '0.23.20-1',
    'libpcre1' => '8.45-1',
    'libpcre2_8_0' => '10.39-1',
    'libpipeline1' => '1.5.3-1',
    'libpng16' => '1.6.37-1',
    'libpopt-common' => '1.18-1',
    'libpopt0' => '1.18-1',
    'libreadline7' => '8.1-2',
    'libsigsegv2' => '2.10-2',
    'libSM6' => '1.2.3-1',
    'libsmartcols1' => '2.33.1-2',
    'libssl1.1'=>'1.1.1n-1',
    'libstdc++6'=>'11.2.0-1',
    'libtasn1_6' => '4.14-1',
    'libuuid1' => '2.33.1-2',
    'libX11_6' => '1.7.3.1-1',
    'libXau6' => '1.0.9-1',
    'libXaw7' => '1.0.14-1',
    'libxcb1' => '1.14-1',
    'libXdmcp6' => '1.1.3-1',
    'libXext6' => '1.3.4-1',
    'libXft2' => '2.3.4-1',
    'libXinerama1' => '1.1.4-1',
    'libXmu6' => '1.1.3-1',
    'libXpm4' => '3.5.13-1',
    'libXrender1' => '0.9.10-1',
    'libXt6' => '1.2.1-1',
    'login' => '1.13-1',
    'man-db' => '2.10.2-1',
    'mintty' => '3.6.0-1',
    'ncurses' => '6.1-1.20190727',
    'openssl' => '1.1.1n-1',
    'p11-kit' => '0.23.20-1',
    'p11-kit-trust' => '0.23.20-1',
    'p7zip' => '15.14-2',
    'rebase' => '4.5.0-1',
    'run' => '1.3.4-2',
    'sed' => '4.8-1',
    'tar' => '1.34-1',
    'terminfo' => '6.1-1.20190727',
    'terminfo-extra' => '6.1-1.20190727',
    'tzcode' => '2022a-1',
    'tzdata' => '2022a-1',
    'util-linux' => '2.33.1-2',
    'vim-minimal' => '8.2.4372-1',
    'which' => '2.20-2',
    'xz' => '5.2.5-1',
    'zlib0' => '1.2.11-1',
    'zstd' => '1.5.2-1',
];

$cygwinSetupOptions=[
    '--no-shortcuts',
    '--quiet-mode',
    '--root'=>'"__DIR__"',
    '--arch' => 'x86_64',
    '--packages' => $packages
];
$cygwinSetupDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dependencies'.DIRECTORY_SEPARATOR.'win64'.DIRECTORY_SEPARATOR;
$cygwinSetupBinary="setup-x86_64.exe";
$cygRootDir=__DIR__."/cygroot";
#if(is_dir($cygRootDir)) rmdir($cygRootDir);
#"./setup-x86_64.exe -v - -C Base --no-admin --local-package-dir ./local -q --root ./cygroot  --no-shortcuts  -D";
$swoolePath=$cygwinSetupDir."bin".DIRECTORY_SEPARATOR;
$swooleBinary="swoole-cli.exe  -r \"echo 'test';\"";

$out="";
$exitCode=0;
$cmd = $cygwinSetupDir . $cygwinSetupBinary." -h";
$cmd=$swoolePath.$swooleBinary;
echo $cmd;
exec($cmd,$cmd,$exitCode);
print_r($out);
print_r($exitCode);

