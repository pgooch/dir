<html>
<head>
	<title>Directory Image Resizer</title>
	<script type="text/javascript" src="./div.js"></script>
	<link rel="stylesheet" href="./example.css" />
</head>
<body>
	<div class="wrap">
		<h1>Directory Image Resizer Example</h1>
		<p>The directory image resizer provides a simple way to to resize images, both local and remote, with clean inline commands and tidy URLs. But showing is easier than explaining, so lets start with this image of a somewhat disgruntled monkey.</p>
		<div class="image">
			<img src="./images/monkey.jpg" alt="Disgruntled Monkey" width="407px" height="400px" />
			<a href="./images/monkey.jpg">./images/monkey.jpg</a>
		</div>
		<p>Maybe he just doesn't like having his picture plaster so big on a website. I know, lets make it smaller, say, 80px by 80px, that would work will for an avatar or something.</p>
		<div class="image">
			<img src="./images/80x80/monkey.jpg" alt="Disgruntled Monkey" width="80px" height="80px" />
			<a href="./images/80x80/monkey.jpg">./images/<span>80x80</span>/monkey.jpg</a>
		</div>
		<p>Much better, he's still disgruntled, but at least he's not so in your face about it. It can also handle remote images as well, for that take a look at these cute little puppies.</p>
		<div class="image">
			<img src="http://i.imgur.com/5bMbobU.jpg" alt="Cute Puppies" width="960px" height="540px" />
			<a href="http://i.imgur.com/5bMbobU.jpg">http://i.imgur.com/5bMbobU.jpg</a><br/>
			<i>Note: This image is displayed at 50% scale.</i>
		</div>
		<p>Adorable. But we need a square image, and one thats not quite so large, never fear DIR is here.</p>
		<div class="image">
			<img src="./images/600x600/http://i.imgur.com/5bMbobU.jpg" alt="Cute Puppies" width="600px" height="600px" />
			<a href="./images/600x600/http://i.imgur.com/5bMbobU.jpg">./images/<span>600x600</span>/http://i.imgur.com/5bMbobU.jpg</a>
		</div>
		<p>Oh No! That puppy on the left is getting cut in half, luckily we can do more than just crop the image, lets we what options we have.</p>
		<div class="type">
			<div class="image">
				<img src="./images/200x200d/http://i.imgur.com/5bMbobU.jpg" alt="Cute Puppies Distored" width="200px" height="200px" />
				Distorted
			</div>
			<div class="image">
				<img src="./images/200x200p/http://i.imgur.com/5bMbobU.jpg" alt="Cute Puppies Padded" width="200px" height="200px" />
				Padding
			</div>
			<div class="image">
				<img src="./images/200x200n/http://i.imgur.com/5bMbobU.jpg" alt="Cute Puppies Nearest" width="200px"/>
				Nearest
			</div>
			<div class="image">
				<img src="./images/200x200c/http://i.imgur.com/5bMbobU.jpg" alt="Cute Puppies Cropped" width="200px" height="200px" />
				Crop
			</div>
		</div>
		<p>Wonderful, lets go with nearest<p>
		<div class="image">
			<img src="./images/600x600n/http://i.imgur.com/5bMbobU.jpg" alt="Cute Puppies Nearest" width="600px"/>
			<a href="./images/600x600n/http://i.imgur.com/5bMbobU.jpg">./images/<span>600x600n</span>/http://i.imgur.com/5bMbobU.jpg</a>
		</div>
		<p>Excellent. But what about images with transparencies you ask, well look at fry, just to give someone some money.</p>
		<div class="image">
			<img src="./images/trans-bg.png" alt="Take Fry's Money" width="1000px" />
			<a href="./images/trans-bg.png">./images/trans-bg.png</a>
		</div>
		<p>we can resize that down to a nice little square and keep those transparent backgrounds, this time we'll use the padding method<p>
		<div class="image">
			<img src="./images/300x300p/trans-bg.png" alt="Cute Puppies" width="300px" height="300px" />
			<a href="./images/300x300p/trans-bg.png">./images/<span>300x300p</span>/trans-bg.png</a>
		</div>
		<p>In addition to resizing however you like Directory Image Resizer also caches resized images to save time. The cache image will always be loaded unless it's source has changed, which is checked with a simple MD5 comparison. Other features include support for HiDpi Devices with a tiny bit of javascript to get the users Pixel Ratio. One thing to remember is that if you are using the retina support your going to have to give your images a width and/or height so they don't go all out of proportion and mess things up, but your doing that already right?</p>
		<p>If you want to use the Directory Image Resizer, read more about it in it's real documentation, or just want to poke around it's insides, you can find it <a href="https://github.com/pgooch/dir" target="_blank">right here on GitHub</a>.</p>
		<div class="footer">
			Last Updated February 18th, 2014<br/>
			<a href="mailto:phillip.gooch@gmail.com">phillip.gooch@gmail.com</a>
		</div>
	</div>
	<a href="https://github.com/pgooch/dir" class="github"><img src="https://github-camo.global.ssl.fastly.net/a6677b08c955af8400f44c6298f40e7d19cc5b2d/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f677261795f3664366436642e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_gray_6d6d6d.png"></a>
</body>
</html>