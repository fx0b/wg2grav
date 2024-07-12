# WP to Grav Exporter

WP to Grav Exporter is a WordPress plugin that exports your posts into a format that can be imported by Grav CMS. The exported posts include categories and tags.

## Installation

1. Upload the `wp-to-grav-exporter` directory to the plugin directory of your WordPress installation (`wp-content/plugins/`).

2. Activate the plugin in the WordPress Dashboard under `Plugins`.

## Usage

1. In the WordPress Dashboard, go to `Tools -> WP to Grav Exporter`.

2. Click the `Export` button to export your posts.

## How It Works

- The plugin creates a `grav-export` directory in the upload directory of your WordPress installation.
- Each post is saved in its own subdirectory.
- Each directory contains a YAML file with metadata (title, date, categories, and tags) and a Markdown file with the post content.
- All files are compressed into a ZIP file for download.

## Example of an Exported YAML File

```yaml
---
title: "Post Title"
date: "2023-07-01 10:00:00"
categories:
  - Category 1
  - Category 2
tags:
  - Tag 1
  - Tag 2
---
