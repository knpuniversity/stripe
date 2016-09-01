## Validating Coupons via Stripe

Perfect. Now to read the post parameter for code we'll need
to request object. Then we'd say dollar send code equals request error request
error get code, and that will get the post parameter whose name is code. Just
in case somebody hits submit with it empty, let's add a little validation
saying if not code this error add flash error. We'll say missing coupon code.
Then we will redirect back to our checkout page with that error. Cool, so at
this point all we need to do is go and talk to Stripe and ask them, "Is this a
valid coupon code in your system?" If it is, we'll add it to our shopping cart
and then we can use it later on checkout. As I just showed you, coupon is
actually a object in Stripe's API, so we just need to make an API request out
to fetch a specific coupon. As usual, we're going to do that inside of our
Stripe client. At the bottom here let's create a new public function called
find coupon.

We'll pass it the coupon code. Here we'll just return Stripe/coupon::retrieve
and that will pass us the code, which is the primary key for the coupon. Now
inside of the `OrderController` we can say Stripe coupon equals this arrow get
Stripe onto store client arrow find coupon and code. If that is an invalid
code, that's going to throw and exception and we'll handle that in a second.
Just for right now, let's dump that Stripe coupon and have a die statement so
we can see what it looks like. All right, go back, refresh, hit "I have a
coupon code," and let's put in our CHEAP SHEEP, and that is case sensitive.
Okay, cool so it found our coupon object. In field incentive values it has the
ID, it also has the amount off in cents, which is going to be really important
for us. It also has other information like duration in case you are supporting
recurring duration and you want to be able to tell the user this is going to be
applied once or many times. You might read and use that off right there. For
us, we're going to add this to shopping cart by saying this arrow get shopping
cart.

I've already prepared the shopping cart to have a place where it stores the
discounts, so just say set coupon code, and what it wants is it wants what the
actual code is. The second argument here is the value in dollars for that code.
Lets use Stripe coupon arrow amount underscore off divided by a hundred to put
that into dollars. We're just reading this amount off here to put it into fifty
dollars. Of course we'll add a flash message coupon applied, and then redirect
back to our checkout page. Now I'll go back, and we can just refresh this to
resubmit that. Boom, instantly we see coupon applied. You don't see any
evidence of that in our cart yet, so it's not really obvious if my shopping
cart is working. Let me tell you, inside of our checkout template when we print
the total we call it cart dot total. The way that I made the shopping cart,
when call up a total on it it totals up all of your products and all of your
subscription plans, but it doesn't take into account your discounts. I did that
because the total is the amount that we actually want to tell Stripe to charge
us, to charge the user.

You'll see why in a second. For displaying the user how much they're going to
get charged, we have another method here called get total with discount, which
actually goes and subtracts the coupon code value. To see the update down here
we'll say total with discount. Now that shows forty-nine dollars. Now, also to
be a little more friendly let's add an end statement here that says if cart dot
coupon code then we know that we're going to want to show a little row with
that in it. Let's print out cart dot coupon code here. Then down here we'll
print out cart dot coupon code value. Maybe even a little minus in front. Now,
refresh, now that looks really nice. It'll be nicer with the word coupon in
front. We've validated the coupon with Stripe, we attached the coupon to our
shopping cart, and now when we check out we need to tell Stripe to apply this
coupon code to their account. Let's do that now.
