# Claude Commands

This directory contains custom slash commands for Claude Code.

## Creating a Command

Create a markdown file named after the command (e.g., `review.md` for `/review`).

The file contents become the prompt that Claude receives when the command is invoked.

## Template Variables

You can use `$ARGUMENTS` in your command file to capture additional input from the user.

## Example

```markdown
# review.md
Review the following code for potential issues:
$ARGUMENTS
```

Usage: `/review src/components/Map.vue`
