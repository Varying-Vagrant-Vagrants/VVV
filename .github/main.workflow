action "PHP Lint" {
  uses = "michaelw90/PHP-Lint@1.0.0"
}

action "Standard checks" {
  uses = "cored/standard-action@0.0.1"
}

workflow "ShellCheck Audit" {
  on = "push"
  resolves = ["ShellCheck-Linter-Action"]
}

action "ShellCheck-Linter-Action" {
  uses = "zbeekman/ShellCheck-Linter-Action@v1.0.1"
  env = {
    ALWAYS_LINT_ALL_FILES = "true" # current default
  }
}
