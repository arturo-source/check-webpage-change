# Check webpage changes
### A really simple php script to notify html changes in static pages

Have you ever wanted to be aware of the changes of a website? You may want to see how the price of a product that interests you evolves, or you know what changes you want to see!

With this simple script you can do it in just a few seconds. Let's look at the simple steps to configure the script. 

First, you need to have installed PHP interpreter, and crontab on your computer or your server.

### Variables

The variables you'll need to change are:
- $url **(required)**
- $xpath_node **(required)**
- $chatID (optional, for telegram notify)
- $token (optional, for telegram notify)

Get url is very easy, you can copy it from the top of the browser.
Get xpath is a little harder, once you're in the webpage, you have to right click on it. Then you select "Inspect" option. Then, you'll see an item as next one:

![This is an image](https://myoctocat.com/assets/images/base-octocat.svg)

Now you have to select the html item in the webpage, left click on that.

![This is an image](https://myoctocat.com/assets/images/base-octocat.svg)

Then, the html code will have been marked, so your last step is to right click on it, and select "Copy" > "XPath"

![This is an image](https://myoctocat.com/assets/images/base-octocat.svg)

The final step is pasting it on php script and you'll have the script configured. But now you have to set up the notify.

### Notifications
It's really easy too, if you've ever used Telegram. I asume you have telegram account and a client to use it.
1. Create a bot. (Talk to @BotFather, it will guide you)
2. Copy bot token. (You can paste it now on php script, otherwise you will have to do it later)
3. Talk your new bot. You can talk it directly, or create a group (or channel) with your friends and add the bot there.
4. Access next url on your browser (don't forget change <token> for your token): [https://api.telegram.org/bot<token>/getUpdates](https://api.telegram.org/bot<token>/getUpdates)

You will get a json like the next one:
  
![This is an image](https://myoctocat.com/assets/images/base-octocat.svg)
  
Then you can pick the chatID and paste it in the php script too. And you'll have script totally configured. But now you have to decide how often you want to be notified.
  
### Set up crontab
Crontab is a really usefull tool that you may have installed in your Linux computer or server. It helps you to do recurring tasks automatically. And it's really easy to set up, but your first time use may will be confusing. You can access [this page to configure crontab easily](https://crontab.guru/).
The most common option will be each day, at 12:00 for example, so you will type `0 12 * * *` next to the command. But maybe you want to execute it always you turn on the computer, then you will type `@reboot` next to the command.
 
To open cron configuration you will open a terminal and type `crontab -e`, it allows you to edit cron configurations. You will open a file with an editor, it may will be `nano`. So you only have to paste the next command and let it be:
```
12 0 * * * php /route/to/script/check-change.php
```
And finally save set up with `ctrl+o` and close editor with `ctrl+x`.
  
And that's it. Thanks to arrive since here, I hope i could help you.
