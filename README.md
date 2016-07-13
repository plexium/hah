# HAH

HAH Ain't Haml (but it's close) is a PHP parser and renderer for a 
template / domain specific language similar to HAML. It was heavily 
inspired by HAML. I liked the general concept but wanted to take it
a little further by minimizing the number of language constructs you
needed to know as well as make it easier to use in a PHP environment.

Here's a small example of a HAH document.

    <!html
    html
      head
        title My example page
        !styles.css
        !functions.js
    
      body
        #page
           #header
             !banner.png( border="0" alt="My example Page" )
             !topmenu.hah( color="#fff" )
               @pagetype= $pagetype
    
             #message=? $special_message
    
           #main.left
             h1= $welcome_message
             p Here's a list pages on the site
             ul
               - foreach ( $pages as $page )
                 li= $page
    
           #footer
             <p>
                This is the <em>footer</em> for my example page.
             </p>
    
        ? ( $loggedin )
          span= "You are logged in as " . $username
        :
          a( href="login" ) Login Here

This HAH document will produce the following code.

  <!DOCTYPE HTML>
  <html>
    <head>
      <title>My example page</title>
      <link href="styles.css" type="text/css" rel="stylesheet" />
      <script src="functions.js" type="text/javascript"></script>
    </head>
    <body>
      <div id="page">
        <div id="header">
          <img src="banner.png" border="0" alt="My example Page" />
          <?php 
            $__subhahdoc = new HahDocument('.topmenu.hah'); 
            $__subhahdoc->set('color',"#fff"); 
            $__subhahdoc->set('pagetype',$pagetype); 
            echo $__subhahdoc; unset($__subhahdoc);  
          ?>
          <?php if (HahNode::pick($special_message) != '') { ?>
            <div id="message">
              <?php echo HahNode::pick($special_message); ?>
            </div>
          <?php } ?>
        </div>
        <div id="main" class="left">
          <h1><?php echo $welcome_message; ?></h1>
          <p>Here's a list pages on the site</p>
          <ul>                      
            <?php foreach ( $pages as $page ) { ?>
              <li><?php echo $page; ?></li>
            <?php } ?>
          </ul>
        </div>
        <div id="footer">         
          <p>This is the <em>footer</em> for my example page.</p>
        </div>
      </div>
      <?php if (( $loggedin )) { ?>
        <span><?php echo "You are logged in as " . $username; ?></span>
      <?php } else { ?>
        <a href="login">Login Here</a>
      <?php } ?>
    </body>
  </html>
  
This example shows a byte reduction from 1,337 to 627. Of course size 
is not the problem HAH is trying to address. HAH is concerned with 
readability and reducing the amount of time and energy required to 
produce PHP templates.
