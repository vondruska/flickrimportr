#FlickrImportr
##Overview

FlickrImportr is an Facebook FBML application to import photos from a Flickr photostream to the native Facebook Photos system.

The application was written by [Steven Vondruska](http://stevenvondruska.com) in 2007 to solve a frustrating problem of having to take the time to upload photos to both Facebook and Flickr.

The application was originally written for my personal use, but I finally released it to the public in 2008. I'm unable to pour the time or energy into the project any longer, and I decided to open source it under GPL.

I thank everyone that used the application over the years. But, I would like to personally thank, [Bret Kuhns](http://www.bretkuhns.com/) for being the beta tester, and letting me abuse his Facebook account from time to time. Also I would like to thank the people that donated to get hosting for FlickrImportr (Anonymous, [Andre Pang](http://algorithm.com.au/), [Bret Kuhns](http://www.bretkuhns.com/), [Corey Blaz](http://www.coreyblaz.com/), [Diego Calderon (COLOMBIA Birding)](http://www.colombiabirding.com/), Mark Sheppard and Paul Albertella).

##Technical Details

FlickrImportr was written with [CakePHP](http://www.cakephp.org). First on 1.0, and has been upgraded over the years to Cake 1.3. While hosted it used mySQL. A database dump of all the database schema can be found in the root of the repository.

The application uses [FBML](https://developers.facebook.com/docs/reference/fbml/) and [FBJS](https://developers.facebook.com/docs/fbjs/). Both technologies have been [deprecated](https://developers.facebook.com/roadmap/) by Facebook will stop working on June 1, 2012. Also, Flickr has [set a deadline](http://code.flickr.com/blog/2012/01/13/farewell-flickrauth/) of July 31, 2012 for all applications to be switched over to the [oAuth authorization workflow](http://www.flickr.com/services/api/auth.oauth.html). To sum this up, things will break on June 1st, and the application will completely cease to function after July 31st.

I personally have come a long way in the last five years, and sometimes it hurts to see how bad the code is. I also realize there is a lack of code commenting, and I've attempted to go back and add the comments in where they are needed. This was also my first application that used the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) concept, and I've also seen how horrible the implementation is in some places.

*This product uses the Flickr API but is not endorsed or certified by Flickr.*
