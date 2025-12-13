# Deployment Checklist - Helpdesk System

## Pre-Deployment (Development)

### Code & Documentation
- [x] Complete code implementation
- [x] Database schema created
- [x] API endpoints implemented
- [x] Documentation written
- [x] Security review done
- [x] Testing completed

### Security
- [x] CSRF protection implemented
- [x] SQL injection prevention
- [x] XSS protection
- [x] Rate limiting
- [x] Password hashing (bcrypt)
- [x] Input validation
- [x] Session management

### Database
- [x] Schema created (database.sql)
- [x] Indexes added
- [x] Default admin created
- [x] Sample FAQ data added
- [x] Cleanup events configured

### Testing
- [ ] Manual testing completed
- [ ] All features tested
- [ ] Edge cases tested
- [ ] Performance tested
- [ ] Security tested

---

## Staging Deployment

### Environment Setup
- [ ] Server OS confirmed (Linux/Windows)
- [ ] PHP 7.4+ installed
- [ ] MySQL 5.7+ installed
- [ ] Web server (Apache/Nginx) configured
- [ ] SSL certificate ready
- [ ] Domain name configured

### Application Setup
- [ ] Project cloned/uploaded
- [ ] .env file configured
- [ ] Database imported
- [ ] File permissions set (755 for folders, 644 for files)
- [ ] Logs folder created (777)
- [ ] Uploads folder created (777)

### Configuration
- [ ] APP_ENV=staging
- [ ] APP_DEBUG=false
- [ ] DB credentials verified
- [ ] SESSION_TIMEOUT configured
- [ ] ALLOWED_EXTENSIONS reviewed
- [ ] RATE_LIMIT values configured
- [ ] Email settings (if applicable)

### Testing
- [ ] Landing page accessible
- [ ] Create ticket works
- [ ] Chat functionality works
- [ ] Admin login works
- [ ] Dashboard displays correctly
- [ ] FAQ management works
- [ ] File upload works
- [ ] Rate limiting works
- [ ] Error handling works

### Performance
- [ ] Page load time < 2s
- [ ] Database queries optimized
- [ ] No memory leaks
- [ ] Session cleanup working
- [ ] Logs rotation working

---

## Production Deployment

### Security Hardening
- [ ] .env file not in git
- [ ] No debug mode enabled
- [ ] HTTPS enforced
- [ ] Security headers configured
- [ ] Rate limiting enabled
- [ ] CORS configured if needed
- [ ] File upload restrictions set

### Database
- [ ] Regular backup scheduled
- [ ] Database user has limited privileges
- [ ] Max connections configured
- [ ] Slow query log enabled
- [ ] Backup retention policy set

### Monitoring
- [ ] Error logging configured
- [ ] Activity logging enabled
- [ ] Performance monitoring setup
- [ ] Disk space monitoring
- [ ] Database monitoring
- [ ] Uptime monitoring

### Maintenance Plan
- [ ] Weekly backup schedule
- [ ] Monthly security updates
- [ ] Quarterly performance review
- [ ] Annual disaster recovery test
- [ ] Support contact established
- [ ] Emergency procedures documented

### Backup & Recovery
- [ ] Backup script created
- [ ] Backup location secured
- [ ] Recovery procedure tested
- [ ] Backup retention policy set
- [ ] Off-site backup location

### Documentation
- [ ] Admin manual prepared
- [ ] User guide prepared
- [ ] API documentation
- [ ] Troubleshooting guide
- [ ] Emergency procedures
- [ ] Contact information updated

### Communication
- [ ] Users notified of go-live
- [ ] Admin trained
- [ ] Support team trained
- [ ] IT team briefed
- [ ] Emergency contacts listed

---

## Post-Deployment

### First Week
- [ ] Monitor error logs daily
- [ ] Monitor system performance
- [ ] User feedback collected
- [ ] Issues documented
- [ ] Quick fixes deployed if needed
- [ ] Database verified
- [ ] Backup verified

### First Month
- [ ] Performance tuning done
- [ ] User training completed
- [ ] FAQ updated based on support requests
- [ ] Security audit completed
- [ ] Optimization implemented

### Ongoing (Monthly)
- [ ] Database optimization
- [ ] Activity logs reviewed
- [ ] Performance metrics checked
- [ ] Security updates applied
- [ ] Backup integrity verified
- [ ] FAQ updated
- [ ] User feedback addressed

---

## Key Admin Passwords to Change

**DEFAULT CREDENTIALS (Change immediately!):**
```
Admin Username: admin
Admin Password: admin123
Database User: root
```

**How to change admin password:**
1. Generate new bcrypt hash:
   ```
   php -r "echo password_hash('new_password', PASSWORD_BCRYPT);"
   ```

2. Update database:
   ```sql
   UPDATE admins SET password = '[NEW_HASH]' WHERE username = 'admin';
   ```

---

## Critical Files & Directories

**Protect these files:**
- `.env` - Database credentials
- `logs/` - Sensitive activity logs
- `public/uploads/` - User uploads
- `database.sql` - Schema backup

**Permissions:**
```bash
# Files: 644 (read-write for owner, read for others)
find . -type f -exec chmod 644 {} \;

# Directories: 755 (read-write-execute for owner, read-execute for others)
find . -type d -exec chmod 755 {} \;

# Special: logs and uploads need write access
chmod 777 logs/
chmod 777 public/uploads/
```

---

## Monitoring Checklist

### Daily
- [ ] Check error logs
- [ ] Verify backup completed
- [ ] Monitor disk space
- [ ] Review support tickets

### Weekly
- [ ] Performance review
- [ ] Security log audit
- [ ] Database maintenance
- [ ] Cleanup old files

### Monthly
- [ ] Full security audit
- [ ] Performance tuning
- [ ] Backup restoration test
- [ ] User satisfaction survey

---

## Rollback Plan

If critical issues occur:

1. **Immediate**: Switch to backup version
2. **Short-term**: Restore database from backup
3. **Long-term**: Analyze root cause and implement fix

**Rollback command:**
```bash
mysql mtsnmaja_helpdesk < backup.sql
```

---

## Support Contacts

**In case of emergency:**
- IT Admin: [phone/email]
- Database Admin: [phone/email]
- System Owner: [phone/email]

---

**Status**: Ready for Deployment  
**Version**: 1.0  
**Date**: December 2025
