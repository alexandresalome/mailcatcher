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

    public function testMultiPartWithEmbeddedImage()
    {
        $message = <<<EOF
MIME-Version: 1.0
Sender: matt@example.com
Date: Fri, 31 Jan 2014 13:04:04 +0000
Subject: Testing multipart with embedded image
From: Matt Parker <matt@example.com>
To: Matt Parker <mattp@example.com>
Content-Type: multipart/alternative; boundary=f46d0447861ff9929f04f143ce67


--f46d0447861ff9929f04f143ce67
Content-Type: text/plain; charset=ISO-8859-1

this is *some bold text*
 and some normal.


--f46d0447861ff9929f04f143ce67
Content-Type: multipart/related; boundary=f46d0447861ff9929a04f143ce65


--f46d0447861ff9929a04f143ce65
Content-Type: text/html; charset=ISO-8859-1
Content-Transfer-Encoding: quoted-printable

<div dir=3D"ltr">this is <b>some bold text</b>=A0<div dir=3D"ltr"><div></div>
<div style=3D"display:inline"></div></div>
<img class=3D"center" src=3D"cid:7cc6d387fc99f96a7ef17d905df333f6" alt=3D"Placeholder">
<div>and some normal.</div><div><br></div></div>


--f46d0447861ff9929a04f143ce65
Content-Type: image/png; name=1x1.png
Content-Transfer-Encoding: base64
Content-Disposition: inline; filename=1x1.png
Content-ID: <7cc6d387fc99f96a7ef17d905df333f6>

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

--f46d0447861ff9929a04f143ce65--


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
