# Security Policy

## Supported Versions

We release security updates for the following versions of CodeSnoutr:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

**Please do not report security vulnerabilities through public GitHub issues.**

We take security seriously. If you discover a security vulnerability, please report it to us privately.

### How to Report

Send a detailed email to: **security@example.com**

Include the following information:
- Description of the vulnerability
- Steps to reproduce the issue
- Potential impact and affected versions
- Any suggested fixes or mitigations
- Your contact information for follow-up

### What to Expect

1. **Acknowledgment**: We'll acknowledge your report within 48 hours
2. **Investigation**: We'll investigate and validate the vulnerability
3. **Timeline**: We'll provide a timeline for the fix
4. **Resolution**: We'll release a security patch and advisory
5. **Credit**: We'll credit you in the security advisory (if desired)

### Security Response Timeline

- **Critical vulnerabilities**: Fixed within 7 days
- **High severity**: Fixed within 14 days  
- **Medium/Low severity**: Fixed in next regular release

## Security Best Practices

When using CodeSnoutr, follow these security practices:

### Configuration Security

1. **Protect API Keys**:
   ```php
   // Never commit API keys to version control
   'openai_api_key' => env('OPENAI_API_KEY'),
   ```

2. **Database Security**:
   - Use proper database permissions
   - Keep scan results secure
   - Regularly clean old scan data

3. **File System Access**:
   - Limit scan paths to necessary directories
   - Exclude sensitive files from scanning
   - Use proper file permissions

### Deployment Security

1. **Environment Configuration**:
   ```env
   # Use strong database credentials
   DB_PASSWORD=strong_random_password
   
   # Secure API keys
   OPENAI_API_KEY=your_secure_api_key
   
   # Limit debug mode in production
   CODESNOUTR_DEBUG=false
   ```

2. **Web Interface Security**:
   - Use HTTPS in production
   - Implement proper authentication
   - Restrict access to authorized users only

3. **Scanning Security**:
   - Exclude vendor and third-party directories
   - Limit file size and scanning scope
   - Monitor scanning resource usage

### Code Security

When extending CodeSnoutr:

1. **Input Validation**:
   ```php
   // Always validate file paths
   if (!$this->isValidPath($filePath)) {
       throw new InvalidArgumentException('Invalid file path');
   }
   ```

2. **Output Sanitization**:
   ```php
   // Sanitize code snippets before display
   $snippet = htmlspecialchars($codeSnippet, ENT_QUOTES, 'UTF-8');
   ```

3. **Permission Checks**:
   ```php
   // Check file permissions before scanning
   if (!is_readable($filePath)) {
       throw new RuntimeException('File not accessible');
   }
   ```

## Known Security Considerations

### File System Access

- CodeSnoutr reads source code files for analysis
- Scanner excludes sensitive files by default
- Configure `excluded_paths` to protect sensitive directories

### Data Storage

- Scan results may contain code snippets
- Issues descriptions may reference sensitive information
- Configure data retention policies appropriately

### AI Integration

- Code snippets are sent to OpenAI for analysis (when enabled)
- Disable AI features for sensitive codebases
- Review OpenAI's data usage policies

### Network Requests

- OpenAI API integration requires internet access
- API keys are transmitted over HTTPS
- No other external network requests are made

## Vulnerability Categories

We address vulnerabilities in these categories:

### Critical
- Remote code execution
- SQL injection
- Authentication bypass
- Sensitive data exposure

### High  
- Cross-site scripting (XSS)
- Cross-site request forgery (CSRF)
- Local file inclusion
- Privilege escalation

### Medium
- Information disclosure
- Denial of service
- Session management issues

### Low
- Minor information leaks
- Non-exploitable bugs
- Best practice violations

## Security Features

CodeSnoutr includes built-in security features:

### Input Validation
- File path validation and sanitization
- Configuration parameter validation
- User input sanitization

### Output Protection
- HTML entity encoding
- Code snippet sanitization
- Safe rendering in templates

### Access Control
- File system permission checking
- Path traversal prevention
- Scope limitation

### Error Handling
- Safe error messages
- Log security events
- Fail securely

## Third-Party Dependencies

We monitor security advisories for:
- Laravel framework
- Livewire components
- PHP Parser library
- Symfony components
- OpenAI PHP client

Run security audits:
```bash
composer audit
```

## Security Tools Integration

CodeSnoutr can integrate with security tools:

### Static Analysis
- Can scan its own codebase for vulnerabilities
- Integrates with existing security workflows
- Provides security rule engines

### CI/CD Integration
- Include security scans in build pipelines
- Export results for security dashboards
- Fail builds on critical issues

## Contact

For security-related inquiries:
- **Email**: security@example.com
- **PGP Key**: [Available on request]
- **Response Time**: Within 48 hours

For general support:
- **GitHub Issues**: Bug reports and feature requests
- **GitHub Discussions**: General questions

## Acknowledgments

We thank the security community for responsible disclosure and contributions to CodeSnoutr's security.

---

**Last Updated**: August 18, 2025  
**Next Review**: November 18, 2025
