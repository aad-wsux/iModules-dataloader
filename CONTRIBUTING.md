# How to contribute
👍🎉 First off, thanks for taking the time to contribute! 🎉👍

The following is a set of guidelines for contributing to this project. These are mostly guidelines, not rules. Use your best judgment, and feel free to propose changes to this document in a pull request.

## Code of Conduct
This project and everyone participating in it is governed by the [Code of Conduct](https://github.com/aad-wsux/iModules_dataloader/blob/master/CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code. Please report unacceptable behavior to aad-wsux@cornell.edu.

## What should I know before I get started?
Read the [Readme](https://github.com/aad-wsux/iModules_dataloader/blob/master/README.md) first to understand the project.

## Reporting Bugs
Bugs are tracked as [GitHub issues](https://guides.github.com/features/issues/). Create an issue and provide the following information:

- Description

- Steps to Reproduce
1.
2.
3.

- Expected behavior:

- Screenshots if applicable

- System information

- Additional Information

## Submitting changes
Please send a GitHub Pull Request with a clear list of what you've done (read more about [pull requests](https://help.github.com/en/pull-requests)). Please follow our coding conventions (below) and make sure all of your commits are atomic (one feature per commit).  It is recommended that you use our [pull request template](https://github.com/aad-wsux/iModules_dataloader/blob/master/.github/pull_request_template.md).

Always write a clear log message for your commits. One-line messages are fine for small changes, but bigger changes should look like this:
```

$ git commit -m "A brief summary of the commit
> 
> A paragraph describing what changed and its impact."

```
After you submit your pull request, verify that all [status checks](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/about-status-checks) are passing.

While the prerequisites above must be satisfied prior to having your pull request reviewed, the reviewer(s) may ask you to complete additional design work, tests, or other changes before your pull request can be ultimately accepted.

## Git Commit Messages
- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

## Coding conventions
- Indent using tabs.
- Put spaces after list items and method parameters ([1, 2, 3], not [1,2,3]), around operators (x += 1, not x+=1), and around hash arrows.
- Always use cwd-relative paths rather than root-relative paths in image URLs in any CSS. So instead of url('/images/blah.gif'), use url('../images/blah.gif').
- This is open source software. Consider the people who will read your code, and make it look nice for them.