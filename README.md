WordCamp Talks
==============

If you need to manage a "Call for Speakers" for a WordCamp you are an organizer of, this plugin can make you save some time and ease your life.

Candidates will be able to sign up to your WordPress site using a form including the custom contact methods you can easily generate from the "Talks > Settings" Administration screen as shown below:

![Signup Form](https://cldup.com/CdigRMNoFE.png)

As you can see, you just need to add a comma separated list of contact methods label into the Public or Private user profile fields, save the settings and then you'll be able to activate the checkboxes of your choice in the "Fields to add to the signup form" option.

NB: if you are using WordPress 4.7 and you have more than one language available in your WordPress config, candidates will also be able to select their prefered language for the strings used in the plugin from this signup form.

Then, once logged in they will be able to submit as many talk as they wish using the [submit form](https://cloudup.com/cbfQ4jgXtU3). You can create new categories to tidy all this creativity.

![Regular categories](https://cldup.com/nnMX5CIqE3.png)

Using "flat" (without a hierarchy) categories will display them into a section called "Categories" of the submit form. But you can also choose to use a one level hierarchy in which the parent will be used as the section and the children as checkboxes like show below:

![Hierarchical categories](https://cldup.com/R1z5y2oTJ8.png)

As you can see the title section is using the parent category name and the additional information is using the parent category description.

The talks will be published privately so that only the candidate, the Site Administrator or users having one of the 2 specific rating roles can read and evaluate the talks. These 2 roles are:

+ The Rater: users having this role cannot publish talks but only evaluate them (comment/rate) and they can view the candidates profiles.
+ The Blind Rater: users having this role cannot publish talks but only evaluate them (comment/rate) and they *cannot* view the candidates profiles.

Raters and Admins can use the comments to discuss directly on the talk, the talk's author won't see them. Raters can also use the built in star rating system to progressively build a list of the most promising talks for your WordCamp. From the main archive page of the plugin (usually available at site.url/talks), you will be able to reorder talks according to the number of comments or the average rate they got.

![Workflow](https://cldup.com/YTbC6TQB6o.png)

Admins can also use a mini workflow to build their selection. It's also possible to export a comma separated list of talks clicking on the spreadsheet dashicon next to the Status views.

When you begin to have a lot of submissions, it can be tricky to remember the talks you already rated. That's the reason why @jennybeaumont had the idea to include a specific tab into the Rater's user profile: the "To rate" tab:

[Workflow](https://cldup.com/BeMdU2rb1B.png)

Thanks to her you won't miss a talk!

Requirements
------------

It's a WordPress plugin requiring at least WordPress 4.6.1.
Tested up to WordPress 4.7.

Cloning/Downloading
-------------------

If you're familiar with git you can simply go at the root of you plugins directory and do:

```
git clone https://github.com/imath/wordcamp-talks.git

```

If you downloaded the master archive or one of the release tags. Make sure to rename the main folder of the plugin to 'wordcamp-talks' before activating it.
