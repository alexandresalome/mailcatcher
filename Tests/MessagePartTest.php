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



    public function testMultiPartWithQuotedBoundary ()
    {
        $message = <<<EOF
MIME-Version: 1.0
Sender: matt@example.com
Date: Fri, 31 Jan 2014 13:04:04 +0000
Subject: test
From: Matt Parker <matt@example.com>
To: Matt Parker <mattp@example.com>
Content-Type: multipart/mixed; boundary="=_f46d0447861ff9929f04f143ce67"

--=_f46d0447861ff9929f04f143ce67
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
--=_f46d0447861ff9929f04f143ce67
Content-Type: text/plain; charset=US-ASCII; name="test file.txt"
Content-Disposition: attachment; filename="test file.txt"
Content-Transfer-Encoding: base64
X-Attachment-Id: f_hr3gqtxm0

dGVzdCBmaWxlCg==
--=_f46d0447861ff9929f04f143ce67--
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



    public function testMultiPartWithAMimeFormatWarningMessage ()
    {
        $message = <<<EOF
To: thefilinator@lamplightdb.co.uk
From: thefilinator@lamplightdb.co.uk
Subject: Testing file attachments
Date: Mon, 02 Dec 2013 13:40:48 +0000
Content-Type: multipart/mixed;
 boundary="=_dba29f3ae3414895d994f90b8b013498"
MIME-Version: 1.0

This is a message in Mime Format.  If you see this, your mail reader does not support this format.

--=_dba29f3ae3414895d994f90b8b013498
Content-Type: multipart/alternative;
 boundary="=_6d8e9e6de65fe1320ba67bf1757d7c85"
Content-Transfer-Encoding: 8bit


--=_6d8e9e6de65fe1320ba67bf1757d7c85
Content-Type: text/plain; charset=iso-8859-1
Content-Transfer-Encoding: quoted-printable

Hi, there's a nice file attached to this.=0D=0A=0D=0AWe sent this messag=
e using Lamplight -   click here to unsubscribe.

--=_6d8e9e6de65fe1320ba67bf1757d7c85
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 8bit

Hi, there's a nice file attached to this.<br/><br/><span style="font-size:10px; margin-top: 80px; color:#333;">We sent this message using <a href="http://www.lamplightdb.co.uk" title="Lamplight home page" style="text-decoration:none; color:#333;">Lamplight</a> -  <a href="http://lamplight/en/public/unsubscribe/b/-1/c/2/j/1/p/4a704ccecf4a3335aab6fca48eca00cb6c01909ba6fd37ad1ecf2f24457ef289" title="click here to unsubscribe"  style="text-decoration:underline;"> click here to unsubscribe</a>.</span>

--=_6d8e9e6de65fe1320ba67bf1757d7c85--

--=_dba29f3ae3414895d994f90b8b013498
Content-Type: text/plain
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="testupload.txt"

aGVsbG8sIHRoaXMgaXMgYSBuaWNlIHRleHQgZmlsZSB0byB1cGxvYWQK
--=_dba29f3ae3414895d994f90b8b013498--
EOF;
        $part = new Part;

        $part->loadSource($message);

        $this->assertContains(
            'nice file attached',
            $part->getPart('text/plain')->getContent(),
            'Can we extract the plain text section?'
        );


    }
}
