# Better Agency – CRM Solution

## Project Overview

**Better Agency** is a full-featured CRM and automation platform specifically built for insurance agencies. It helps streamline client communication, manage policies, and automate sales workflows.

---

## Tech Stack

| Layer          | Technology                                                   |
|----------------|---------------------------------------------------------------|
| Frontend       | **Vue.js**, **HTML5**, **CSS3**, JavaScript                  |
| Backend        | **PHP** (CakePHP, Laravel)                                   |
| Database       | **MySQL**                                                    |
| File Storage   | **AWS S3** (document uploads and management)                 |
| Communication  | **Twilio API**, **Nylas API** (for Email & Calendar sync)    |
| Insurance Data | **IVANS API** (policy downloads and transactions)            |
| Integration    | Custom JavaScript Library (bridging Vue.js with PHP backend) |
| API Protocols  | RESTful APIs, AJAX, JSON,                              |

---

## Our Role in Better Agency

We have contributed to multiple mission-critical modules and is responsible for:

- Frontend development using Vue.js
- Backend API design and development in PHP
- Building a **custom JavaScript bridge library** to seamlessly integrate Vue.js with the legacy Cake PHP stack
- Developing and maintaining the **Contact Card**, **Campaign Engine**, **Attachment System**, and **Policy Module**
- Handling data consistency, API versioning, and performance optimization

---

## Key Modules We Developed/Maintained

### 🔹 Contact Card Interface

A central hub where agents can view and manage contact-related data. Tabs include:

1. **Overview** – General contact summary
2. **Emails** – Send and track email communications using Nylas and sendgrid
3. **Texts** – SMS management via Twilio
4. **Notes** – Internal agent notes
5. **Tasks** – Assign and complete tasks
6. **Campaigns** – Manage campaign participation
7. **Attachments** – Upload/manage contact or policy documents
8. **ACORDs** – Store and manage standardized insurance forms
9. **Logs** – Track all user activity
10. **Opt In/Out** – Manage contact communication preferences

---

### 🔹 Campaign Automation

- Sequence-based sending of Emails, SMS, and Tasks
- Automatic handling of DNC (Do Not Contact) rules
- Delayed/rescheduled messaging logic
- Real-time status tracking per contact

### 🔹 Policy Management System

We implemented a fully integrated policy module where agents can:
- Add/update insurance policies (Auto, Home, etc.)
- Link policies with contacts and campaigns
- Track policy lifecycles and status changes
- Automate renewal and upsell processes

---

### 🔹 Attachments & Document Handling

- Upload and manage policy-related documents
- Support for PDFs, images, and scanned forms
- Integration with AWS S3 for secure storage
- Version control and preview functionality

---

## Impact & Scalability

- Tens of thousands of insurance agents onboarded
- Millions of contacts managed through the system
- Highly scalable and modular design for long-term growth


