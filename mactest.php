<?

# Three different ways to compute the HMAC in PHP. 
# Since PHP 5.0.12 there is a native method 'hash_hmac', please use this if you can
# For PHP 4, the "Local implementation" may be easiest (but please consider upgrading to PHP5)

$secret = "Kah942*$7sdp0)";
$plaintext = "10000GBP2007-10-20Internet Order 123454aD37dJATestMerchant2007-10-11T11:00:00Z";
# result should be x58ZcRVL1H6y+XSeBGrySJ9ACVo=

# PEAR Crypt_HMAC
# install using "pear install Crypt_HMAC"
require '/usr/share/php/Crypt/HMAC.php';

print "PHP5 native implementation  computed:  "  . base64_encode(hash_hmac('sha1',$plaintext,$secret,true)) . "\n";
print "PEAR Crypt_HMAC             computed:  "  . base64_encode(pack('H*',hmacsha1_pear($secret,$plaintext))) . "\n";
print "Local implementation        computed:  "  . base64_encode(pack('H*',hmacsha1($secret,$plaintext))) . "\n";

function hmacsha1_pear($key,$data) {
	$Crypt_HMAC = new Crypt_HMAC($key, 'sha1');
	return $Crypt_HMAC->hash($data);
}

//Calculate HMAC-SHA1 according to RFC2104
// http://www.ietf.org/rfc/rfc2104.txt
function hmacsha1($key,$data) {
    $blocksize=64;
    $hashfunc='sha1';
    if (strlen($key)>$blocksize)
        $key=pack('H*', $hashfunc($key));
    $key=str_pad($key,$blocksize,chr(0x00));
    $ipad=str_repeat(chr(0x36),$blocksize);
    $opad=str_repeat(chr(0x5c),$blocksize);
    $hmac = pack(
                'H*',$hashfunc(
                    ($key^$opad).pack(
                        'H*',$hashfunc(
                            ($key^$ipad).$data
                        )
                    )
                )
            );
    return bin2hex($hmac);
}

?>
