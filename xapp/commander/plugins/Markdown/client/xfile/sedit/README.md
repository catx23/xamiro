[![Build Status](https://travis-ci.org/jesperronn/stackedit.svg?branch=master)](https://travis-ci.org/jesperronn/stackedit)

StackEdit
=========

StackEdit is a full-featured, open-source Markdown editor based on PageDown, the Markdown library used by Stack Overflow and the other Stack Exchange sites.

Main showcase: https://stackedit.io/.

This fork (Stackedit-editor only)
===================================

Minimal client-side part of Stackedit.

This branch [jesperronn/master](https://github.com/jesperronn/stackedit/tree/master) removes all server-side parts, and special code for handling www.stackedit.io, google analytics, and more.

The goal is that this should provide a clean split-panel editor without extra bells and whistles.

It is meant to be integrated in your own page, as an editor plugin for that particular purpose only.

* [x] Remove server-side parts used only be third-party integration points
* [x] Remove chrome extension/app
* [x] Remove firefox extension/app
* [x] Remove google analytics
* [x] remove special edge-case syntax handling of markdown
  - [x] remove mathjax
  - [x] remove flowchart/uml
  - [x] remove syntax highlighting
* [x] Remove extra css stylesheets, there is only need for one stylesheet here
* [x] Remove all settings and provide some decent defaults.
* [x] Remove monetizeJs
* [x] Remove all third-party integration points:
  - [x] twitter
  - [x] google drive
  - [x] picasa
  - [x] github
  - [x] couchdb
  - [x] dropbox
  - [x] blogger
  - [x] google+
  - [x] ssh
  - [x] tumblr
  - [x] wordpress


All of this functionality is provided in the full version of Stackedit. This branch can easily be extended to provide just the features you need for a specific project.

Furthermore, this fork splits up functionality to make the editor easier to integrate into
existing pages. This fork of Stackedit is meant to be easier to use, easier to extend,
and easier to slim down in case you need it. The following improvements are put in, but
also live as pull-requests for benweet/stackedit project:

* [x] [Integration-firendly Stackedit: Move some 'evil' styles out into separate files](/benweet/stackedit/#705)
* [x] [Relax keyboard triggers: We want reload, not HR](/benweet/stackedit/#704)
* [x] [Extracted theme loading to separate file.](/benweet/stackedit/#652)
* [x] [Possible to override internal Stackedit functionality](/benweet/stackedit/#695)
* [x] [Stackedit modularization: Plugins folders](/benweet/stackedit/#595)

If you want the full version of stackedit, with all the functionality listed above, please go to https://stackedit.io



Support StackEdit:

[![](https://cdn.monetizejs.com/resources/button-32.png)](https://monetizejs.com/authorize?client_id=ESTHdCYOi18iLhhO&summary=true)

> **Note:**
>
> - Documents are stored in the [browser's local storage][1], which means they are not shared between different browsers/computers. Clearing your browser's data may delete all your local documents.
> - Full access to Dropbox or Google Drive is required to be able to import any document in StackEdit. Imported documents are downloaded in your browser and are not transmitted to a server.

### StackEdit can:

 - Manage multiple Markdown documents online or offline
 - Export your documents in Markdown, HTML or PDF and format it using a template
 - Synchronize your Markdown documents in the Cloud
 - Edit existing Markdown documents from Google Drive, Dropbox and your local hard drive
 - Share a link to a Markdown document that renders it in a nice viewer
 - Show statistics about your document
 - Convert HTML to Markdown

### Features:

 - Real-time HTML preview with Scroll Link feature to bind editor and preview scrollbars
 - Markdown Extra/GitHub Flavored Markdown support and Prettify/Highlight.js syntax highlighting
 - LaTeX mathematical expressions using MathJax
 - WYSIWYG control buttons
 - Configurable layout
 - Theming support with different themes available
 - A la carte extensions
 - Offline editing

### Documentation:

 - [Hello! document][2]
 - [Developer guide][3]
 - [Theming guide][4]

> **NOTE:** This page has been written and published with [StackEdit][5].


  [1]: https://developer.mozilla.org/en-US/docs/Web/Guide/DOM/Storage#localStorage
  [2]: https://github.com/benweet/stackedit/blob/master/public/res/WELCOME.md#welcome-to-stackedit---welcome "Welcome document"
  [3]: https://github.com/benweet/stackedit/blob/master/doc/developer-guide.md#developer-guide "Developer guide"
  [4]: https://github.com/benweet/stackedit/blob/master/doc/theming.md#stackedit-theming-guide "Theming guide"
  [5]: https://stackedit.io/ "StackEdit"
