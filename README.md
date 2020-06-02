The software behind the inaugural (and future?) [Calice Cup](https://calice.snowcrash.fun).

## Requirements

- PHP ^7.1
- A properly configured web server with the `public` directory as its document root
- A MySQL database, with its URL provided as DATABASE_URL in your environment's configuration
- Node, to install front-end dependencies and run scripts

## Installation

`composer install` should be enough to get you going. In production, `composer install --no-dev --optimize-autoloader` will skip developer packages and build a class map for improved performance.

## Usage

This bundle was first used for a relatively simple, team-based MAME score tournament. Over the course of a year it has evolved into something increasingly flexible. At its core, it still provides tools to easily create a tournament, a draft to gather interested players, manage teams, and to record players' scores including provided video URLs, MAME INP files, or screenshots.

Currenly, dangerous operations (deletion of tournaments, teams, scores, etc.) are left to users with the ADMIN role. The capability to create and update tournaments and teams is given to users with the TO (tournament organizer) role. All other users have the capability to accept, decline, or rescind draft invites, create and update scores for tournaments, as well as post "personal best" scores on persistent leaderboards for games from previous tournaments.

## Development Roadmap

The big push at present is to include even more flexibility in the rulesets for tournaments:

- Individual tournaments (no teams)
- Optional ranked points table
- Cutoffs for individual scores instead of only teams

It is also vital to add unit and functional tests to this bundle, and ensure that features developed in the future are done so with a test driven mindset.

# Feature Requests from Players
- compare scores of two specific players across all games as kind of a head to head matchup
- Suggest game(s) to focus on, where you have a chance to increase in rank
- A points breakdown on one's profile page would be handy, to see which games are contributing the most to the total points.
- Displaying tournament rules for a game on that game's tournament page
- A widget based layout for tournament pages, similar to the current stream kit, that makes it easy to track the tournament as a whole from one view