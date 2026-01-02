#!/usr/bin/env bash
# Test suite for vvv_parallel_hook bug fix
# Tests that parallel hooks properly handle background processes
#
# Run via: vagrant ssh -c "sudo bash /srv/provision/tests/test-parallel-hooks.sh"

set -e
source /srv/provision/provision-helpers.sh

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0

# Cleanup function
cleanup_test_files() {
  rm -f /tmp/vvv-hook-test-*.txt
  rm -f /tmp/vvv-hook-test-*.marker
  # Clean up any test hook registrations
  unset VVV_HOOKS_test_sequential_hook
  unset VVV_HOOKS_test_parallel_hook
  unset VVV_HOOKS_test_sequential_hook_50
  unset VVV_HOOKS_test_parallel_hook_50
  unset VVV_HOOKS_test_priority_hook
  unset VVV_HOOKS_test_priority_hook_10
  unset VVV_HOOKS_test_priority_hook_20
}

# Test assertion helpers
assert_file_exists() {
  local file=$1
  local test_name=$2
  TESTS_RUN=$((TESTS_RUN + 1))

  if [[ -f "$file" ]]; then
    echo -e "${GREEN}✓ PASS${NC}: $test_name - File exists: $file"
    TESTS_PASSED=$((TESTS_PASSED + 1))
    return 0
  else
    echo -e "${RED}✗ FAIL${NC}: $test_name - File missing: $file"
    TESTS_FAILED=$((TESTS_FAILED + 1))
    return 1
  fi
}

assert_file_contains() {
  local file=$1
  local expected=$2
  local test_name=$3
  TESTS_RUN=$((TESTS_RUN + 1))

  if [[ -f "$file" ]] && grep -q "$expected" "$file"; then
    echo -e "${GREEN}✓ PASS${NC}: $test_name - File contains '$expected'"
    TESTS_PASSED=$((TESTS_PASSED + 1))
    return 0
  else
    echo -e "${RED}✗ FAIL${NC}: $test_name - File missing or doesn't contain '$expected'"
    if [[ -f "$file" ]]; then
      echo "  Actual content: $(cat "$file")"
    fi
    TESTS_FAILED=$((TESTS_FAILED + 1))
    return 1
  fi
}

# Test hook functions - these will be registered to test hooks

# Test 1: Simple background write with delay
test_background_write_hook() {
  echo "Hook starting: test_background_write_hook"
  # Spawn background process that writes after delay
  # Using longer delay to ensure pkill catches it
  (sleep 2 && echo "background_completed" > /tmp/vvv-hook-test-bg.txt) &
  echo "Hook foreground work completed"
  # Do NOT wait - this tests if background process survives
}
export -f test_background_write_hook

# Test 2: Multiple background processes with different timings
test_multi_background_hook() {
  echo "Hook starting: test_multi_background_hook"
  # Spawn 3 background jobs with staggered completion
  # Using longer delays to ensure pkill catches them
  (sleep 2 && echo "job1" > /tmp/vvv-hook-test-job1.txt) &
  (sleep 2.1 && echo "job2" > /tmp/vvv-hook-test-job2.txt) &
  (sleep 2.2 && echo "job3" > /tmp/vvv-hook-test-job3.txt) &
  echo "Spawned 3 background jobs"
}
export -f test_multi_background_hook

# Test 3: Pipe operation (simulates curl | gpg pattern)
test_pipe_operation_hook() {
  echo "Hook starting: test_pipe_operation_hook"
  # Background pipe operation
  # Using delay to ensure pkill catches it
  (sleep 1.5 && echo "raw_data" | sed 's/raw_data/processed_data/' > /tmp/vvv-hook-test-pipe.txt) &
  echo "Pipe operation backgrounded"
}
export -f test_pipe_operation_hook

# Test 4: Long-running daemon (simulates mailhog)
test_daemon_spawn_hook() {
  echo "Hook starting: test_daemon_spawn_hook"
  # Spawn a "daemon" that writes periodically (simulate mailhog)
  # This daemon writes a marker file then runs for a while
  (echo "daemon_started" > /tmp/vvv-hook-test-daemon.marker && sleep 10) &
  echo "Daemon spawned"
}
export -f test_daemon_spawn_hook

# Test 5: Hook that properly waits (control test)
test_proper_wait_hook() {
  echo "Hook starting: test_proper_wait_hook"
  (sleep 0.2 && echo "waited" > /tmp/vvv-hook-test-waited.txt) &
  local pid=$!
  wait $pid  # Properly wait for background work
  echo "Waited for background work to complete"
}
export -f test_proper_wait_hook

# Main test runner

run_sequential_tests() {
  echo ""
  echo -e "${YELLOW}========================================${NC}"
  echo -e "${YELLOW}Testing SEQUENTIAL hooks (vvv_hook)${NC}"
  echo -e "${YELLOW}========================================${NC}"

  cleanup_test_files

  # Register test hooks
  vvv_add_hook test_sequential_hook test_background_write_hook 50
  vvv_add_hook test_sequential_hook test_multi_background_hook 50
  vvv_add_hook test_sequential_hook test_pipe_operation_hook 50
  vvv_add_hook test_sequential_hook test_daemon_spawn_hook 50
  vvv_add_hook test_sequential_hook test_proper_wait_hook 50

  # Run sequential hooks
  echo "Executing sequential hooks..."
  vvv_hook test_sequential_hook

  # Give background processes time to complete
  echo "Waiting 3 seconds for background processes..."
  sleep 3

  # Verify results
  echo ""
  echo "Verifying sequential hook results..."
  assert_file_contains "/tmp/vvv-hook-test-bg.txt" "background_completed" "Sequential: Background write"
  assert_file_contains "/tmp/vvv-hook-test-job1.txt" "job1" "Sequential: Multi-background job1"
  assert_file_contains "/tmp/vvv-hook-test-job2.txt" "job2" "Sequential: Multi-background job2"
  assert_file_contains "/tmp/vvv-hook-test-job3.txt" "job3" "Sequential: Multi-background job3"
  assert_file_contains "/tmp/vvv-hook-test-pipe.txt" "processed_data" "Sequential: Pipe operation"
  assert_file_exists "/tmp/vvv-hook-test-daemon.marker" "Sequential: Daemon startup"
  assert_file_contains "/tmp/vvv-hook-test-waited.txt" "waited" "Sequential: Proper wait"

  # Kill the daemon
  pkill -f "sleep 10" 2>/dev/null || true
}

run_parallel_tests() {
  echo ""
  echo -e "${YELLOW}========================================${NC}"
  echo -e "${YELLOW}Testing PARALLEL hooks (vvv_parallel_hook)${NC}"
  echo -e "${YELLOW}========================================${NC}"

  cleanup_test_files

  # Register test hooks
  vvv_add_hook test_parallel_hook test_background_write_hook 50
  vvv_add_hook test_parallel_hook test_multi_background_hook 50
  vvv_add_hook test_parallel_hook test_pipe_operation_hook 50
  vvv_add_hook test_parallel_hook test_daemon_spawn_hook 50
  vvv_add_hook test_parallel_hook test_proper_wait_hook 50

  # Run parallel hooks
  echo "Executing parallel hooks..."
  vvv_parallel_hook test_parallel_hook

  # Give background processes time to complete (if they survived pkill)
  echo "Waiting 3 seconds for background processes..."
  sleep 3

  # Verify results
  echo ""
  echo "Verifying parallel hook results..."
  assert_file_contains "/tmp/vvv-hook-test-bg.txt" "background_completed" "Parallel: Background write"
  assert_file_contains "/tmp/vvv-hook-test-job1.txt" "job1" "Parallel: Multi-background job1"
  assert_file_contains "/tmp/vvv-hook-test-job2.txt" "job2" "Parallel: Multi-background job2"
  assert_file_contains "/tmp/vvv-hook-test-job3.txt" "job3" "Parallel: Multi-background job3"
  assert_file_contains "/tmp/vvv-hook-test-pipe.txt" "processed_data" "Parallel: Pipe operation"
  assert_file_exists "/tmp/vvv-hook-test-daemon.marker" "Parallel: Daemon startup"
  assert_file_contains "/tmp/vvv-hook-test-waited.txt" "waited" "Parallel: Proper wait"

  # Kill the daemon
  pkill -f "sleep 10" 2>/dev/null || true
}

# Test priority ordering
test_priority_ordering() {
  echo ""
  echo -e "${YELLOW}========================================${NC}"
  echo -e "${YELLOW}Testing priority ordering${NC}"
  echo -e "${YELLOW}========================================${NC}"

  cleanup_test_files

  # Functions that record timing
  priority_10_hook() {
    date +%s.%N > /tmp/vvv-hook-test-priority-10.txt
    sleep 0.5
  }
  export -f priority_10_hook

  priority_20_hook() {
    date +%s.%N > /tmp/vvv-hook-test-priority-20.txt
  }
  export -f priority_20_hook

  vvv_add_hook test_priority_hook priority_10_hook 10
  vvv_add_hook test_priority_hook priority_20_hook 20

  echo "Executing hooks with priority 10 and 20..."
  vvv_parallel_hook test_priority_hook

  # Verify priority 10 completed before priority 20 started
  if [[ -f "/tmp/vvv-hook-test-priority-10.txt" ]] && [[ -f "/tmp/vvv-hook-test-priority-20.txt" ]]; then
    local time_10=$(cat /tmp/vvv-hook-test-priority-10.txt)
    local time_20=$(cat /tmp/vvv-hook-test-priority-20.txt)

    # Priority 10 should have started before priority 20
    # Add 0.5s to time_10 for the sleep
    local time_10_end=$(awk "BEGIN {print $time_10 + 0.5}")

    TESTS_RUN=$((TESTS_RUN + 1))
    if (( $(awk "BEGIN {print ($time_10_end <= $time_20) ? 1 : 0}") )); then
      echo -e "${GREEN}✓ PASS${NC}: Priority ordering - Priority 10 completed before priority 20"
      TESTS_PASSED=$((TESTS_PASSED + 1))
    else
      echo -e "${RED}✗ FAIL${NC}: Priority ordering - Priorities out of order"
      echo "  Priority 10 start: $time_10"
      echo "  Priority 10 end: $time_10_end"
      echo "  Priority 20 start: $time_20"
      TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
  else
    TESTS_RUN=$((TESTS_RUN + 1))
    echo -e "${RED}✗ FAIL${NC}: Priority ordering - Missing timing files"
    TESTS_FAILED=$((TESTS_FAILED + 1))
  fi
}

# Main execution
echo -e "${YELLOW}╔════════════════════════════════════════╗${NC}"
echo -e "${YELLOW}║  VVV Parallel Hook Test Suite         ║${NC}"
echo -e "${YELLOW}╚════════════════════════════════════════╝${NC}"
echo ""

# Run all tests
run_sequential_tests
run_parallel_tests
test_priority_ordering

# Summary
echo ""
echo -e "${YELLOW}========================================${NC}"
echo -e "${YELLOW}Test Summary${NC}"
echo -e "${YELLOW}========================================${NC}"
echo "Total tests run: $TESTS_RUN"
echo -e "${GREEN}Passed: $TESTS_PASSED${NC}"
echo -e "${RED}Failed: $TESTS_FAILED${NC}"
echo ""

# Cleanup
cleanup_test_files

# Exit with appropriate code
if [[ $TESTS_FAILED -gt 0 ]]; then
  echo -e "${RED}Some tests FAILED${NC}"
  echo ""
  echo "Expected behavior:"
  echo "  - Sequential hooks: ALL tests should pass"
  echo "  - Parallel hooks (BEFORE fix): Background process tests will FAIL"
  echo "  - Parallel hooks (AFTER fix): ALL tests should pass"
  exit 1
else
  echo -e "${GREEN}All tests PASSED${NC}"
  exit 0
fi
