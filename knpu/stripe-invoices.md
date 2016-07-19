# Stripe Invoices

Now we're going to talk about 2 Stripe objects. We have the low-level charges and now we're creating customers, attaching the card to the customer and then charging the Customer which is really cool.

Now we're going to talk about the third object in Stripe which is the Invoice. Here's the idea. You can either just charge a customer, or you can add invoice items to the customer and then create an invoice for them. In practical terms, the difference is that instead of having a charge, all of your charges will live instead of an invoice. This is just a nice way to organize your charges, eventually if you're going to use subscriptions because invoices are usually used for subscriptions.

In fact, that's where you'll find invoice stuff down here under the subscription area but you can use it for anything that you want. Creating an invoice is a two-step process. We're going to create some invoice items first and then create an invoice and then we're going to have the user pay the invoice behind the scenes. To the user it will all look the same, but it's going to count differently inside of Stripes interface.

First, really simple, instead of Stripe's last charge, it's going to be Stripes/invoiceItem. Then below that we're going to add: Invoice=Stripe/Invoice::Create pass that in array, just pass it, our Customer ID of course is going to be: User>GetStripeCustomerID. Now the last step here is Invoice>Pay.

Let's break this down. The first one is going to create an invoice item. The second part is going to create an invoice. What Stripe does is when you create that Invoice, it automatically looks for all the unpaid invoice items and attaches them to the Invoice. The last line is going to actually say: Invoice>Pay. Usually when you create an invoice, Stripe tries to charge the card and pay it automatically but there are some subtle reasons why we want this Invoice>Pay here to make sure that it's paid absolutely right this second.

Let's go back and try this out. Let's go to [Wolf 00:03:20], let's add something to our cart. Put in a fake credit card, fake information and it worked. Here's the cool part. Since we have a customer now, this should have charged this in Customer. Let's refresh the Customer page on Stripe. Check this out. We have two payments down here and we can see the Invoice. We check with the Invoice, the Invoice has line items and then there's still a charge object behind the scene that charges for this Invoice.

Or we can already leverage this to do something cooler because on Sheep Sheer Club you're of course going to want to buy multiple items. Let's get some shears. Let's head down and get some Sheerly Conditioned. There we go now we have 2 items in the cart. If we checked out right now with our current system this would be just one giant line item for $106.00. But now we can do better than that.

Around the Invoice item let's do a for each, over this arrow GetShoppingCart, ShoppingCart>GetProductsAsProduct. In other words let's loop over all the actual products in our shopping cart, let's create invoice items for each of those. All we need to do now is change the amounts to be: Product>GetPriceX100 and then we can even improve the description down here. Instead of this bad first test charge, we can say ProductGetName, so then if we eventually send the user some sort of receipt or invoice it's going to be very easy for us to look on this Stripe invoice and see exactly what we charged them for.

I like this a lot better. Let's try it out. Put in our awesome fake information, hit ENTER. It looks like it worked. Let's go back. I'm on the previous invoice, I'm going to click to go back to the Customer page. We see a second invoice on here for $106.00. Click in that and now we can see the two individual Invoice line items.

Yes, you can just charge people. If you charge customers, and even better if you create invoices with line items, it's just going to create a much, much better accounting system in the long run.
