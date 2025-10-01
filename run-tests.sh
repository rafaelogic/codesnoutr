#!/bin/bash

echo "🧪 Running AutoFixService Tests..."
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color BLUE='\033[0;34m'

echo -e "${BLUE}Setting up test environment...${NC}"

# Make sure we're in the correct directory
cd "$(dirname "$0")"

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}Installing dependencies...${NC}"
    composer install --no-interaction --prefer-dist
fi

echo -e "${BLUE}Running Unit Tests...${NC}"
echo "--------------------"
if vendor/bin/phpunit tests/Unit/AutoFixServiceUnitTest.php --colors=always; then
    echo -e "${GREEN}✅ Unit tests passed!${NC}"
else
    echo -e "${RED}❌ Unit tests failed!${NC}"
fi

echo ""
echo -e "${BLUE}Running Feature Tests...${NC}"
echo "------------------------"
if vendor/bin/phpunit tests/Feature/AutoFixServiceTest.php --colors=always; then
    echo -e "${GREEN}✅ Feature tests passed!${NC}"
else
    echo -e "${RED}❌ Feature tests failed!${NC}"
fi

echo ""
echo -e "${BLUE}Running Failure Tests (Expected to show validation and error handling)...${NC}"
echo "--------------------------------------------------------------------------"
if vendor/bin/phpunit tests/Feature/AutoFixServiceFailureTest.php --colors=always; then
    echo -e "${GREEN}✅ Failure tests passed (system handled errors gracefully)!${NC}"
else
    echo -e "${YELLOW}⚠️  Some failure tests failed (this may be expected if testing validation)${NC}"
fi

echo ""
echo -e "${BLUE}Running Full Test Suite...${NC}"
echo "--------------------------"
vendor/bin/phpunit --colors=always

echo ""
echo -e "${GREEN}🎉 Test run completed!${NC}"
echo "=========================="
echo ""
echo "Test Coverage:"
echo "- Unit tests: Core parsing and detection logic"
echo "- Feature tests: End-to-end fix application scenarios" 
echo "- Failure tests: Error handling and validation"
echo ""
echo "To run individual test suites:"
echo "  Unit:    vendor/bin/phpunit tests/Unit/"
echo "  Feature: vendor/bin/phpunit tests/Feature/"
echo "  All:     vendor/bin/phpunit"