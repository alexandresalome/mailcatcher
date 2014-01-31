<?php

namespace Alex\MailCatcher\Tests;
use Alex\MailCatcher\Mime\Part;

class MessagePartTest extends \PHPUnit_Framework_TestCase
{


    public function testMultiPartWithAttachment()
    {
        $message = <<<EOF
MIME-Version: 1.0
Sender: matt@example.com
Date: Fri, 31 Jan 2014 13:04:04 +0000
Subject: test
From: Matt Parker <matt@example.com>
To: Matt Parker <mattp@example.com>
Content-Type: multipart/mixed; boundary=f46d0447861ff9929f04f143ce67

--f46d0447861ff9929f04f143ce67
Content-Type: multipart/alternative; boundary=f46d0447861ff9929a04f143ce65

--f46d0447861ff9929a04f143ce65
Content-Type: text/plain; charset=ISO-8859-1

this is *some bold text*
 and some normal.

--f46d0447861ff9929a04f143ce65
Content-Type: text/html; charset=ISO-8859-1
Content-Transfer-Encoding: quoted-printable

<div dir=3D"ltr">this is <b>some bold text</b>=A0<div dir=3D"ltr"><div>
</div><div style=3D"display:inline"></div></div>
<div>and some normal.</div><div><br></div></div>

--f46d0447861ff9929a04f143ce65--
--f46d0447861ff9929f04f143ce67
Content-Type: text/plain; charset=US-ASCII; name="test file.txt"
Content-Disposition: attachment; filename="test file.txt"
Content-Transfer-Encoding: base64
X-Attachment-Id: f_hr3gqtxm0

dGVzdCBmaWxlCg==
--f46d0447861ff9929f04f143ce67--
EOF;

        $part = new Part;

        $part->loadSource($message);

        $this->assertContains(
            'this is *some bold text*',
            $part->getPart('text/plain')->getContent(),
            'Can we extract the plain text section?'
        );
        $this->assertContains(
            'this is <b>some bold text</b>',
            $part->getPart('text/html')->getContent(),
            'Can we get the html content?'
        );
    }

}
