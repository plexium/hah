HAH - Hah Ain't Haml
--------------------

0.9
[x] - add !js,css,img easier 
[x] - fix parameters into include command
[x] - change includes to be more runtime ( passed values can't be changed in parent include ) 
[x] - add multiple fall back to ? option ie div=? $error,$default,"blah"
[x] - fix if/else syntax
[x] - added caching
[x] - add some ability to debug

TODO
- syntax checking 
- create a demo that illustrates hah
- maybe add extra ! for csv, xsl, dir ( include-it-all )
- maybe look into a special debugging node that spits out raw code of its children
- standard hah library 

Haha - HAH Assets

form elements, page elements, navigation, ajax components, google maps

forms
	text
	select
	date
	radio
	checkbox
	button
	submit
	hidden
		
demo

standard form elements
- date pickers
- text
- yes/no question
- int range
- money
- 
- question
- common demographic fields
-- first,middle,last name
-- address
-- city
-- state
-- country
-- zip
-- age
-- gender
-- suffix
-- ssn
-- phone
-- email

<!html
html
	head
		title My Hah Test
		!mylib.js
		!mycss.css
	body
		#main.page
			h1 This is <b>my</b> test site.
			
			//and this is a hah comment			
			- foreach ( $colors as $color )
				span( class="colors" )= $color
					@style display:block

		<div>
			all of this gets passed regardless of parsing
		</div>
				
<!DOCTYPE HTML>
<html>
<head>
	<title>My Hah Test</title>
	<script src="mylib.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="mycss.css" />
</head>
<body>
<div id="main" class="page">
	
</div>
</body>
</html>

html
  head
    title My Hah Page
  body
    h1 My <b>Heading</b>
    <p>
      This is a big paragraph which is going to span mutliple lines
      
    </p>

  
  
  
<html>
  <head>
     <title>My Hah page</title>
  </head>
  <body>
     <h1> My heading
  </body>
</html>
  