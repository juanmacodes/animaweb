$path = "d:\ANIMAAVATAR\animaweb\html\wp-content\plugins\anima-social-nexus\anima-social-nexus.php"
$content = Get-Content $path
$newContent = $content[0..804] + $content[1000..($content.Count-1)]
$newContent | Set-Content $path -Encoding UTF8
