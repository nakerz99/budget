# Budget Tracker App - Data Flow Diagrams

## 1. Overall System Data Flow

```mermaid
graph TB
    User[User Interface] --> Frontend[Frontend App]
    Frontend --> API[API Gateway]
    API --> Auth[Authentication Service]
    API --> Transaction[Transaction Service]
    API --> Budget[Budget Service]
    API --> Savings[Savings Service]
    API --> Bills[Bills Service]
    API --> Reports[Reports Service]
    
    Transaction --> DB[(Database)]
    Budget --> DB
    Savings --> DB
    Bills --> DB
    Reports --> DB
    
    DB --> Cache[(Redis Cache)]
    Cache --> API
    
    Frontend --> LocalStorage[(Local Storage)]
    Frontend --> State[Redux Store]
    
    subgraph "External Services"
        Email[Email Service]
        FileStorage[File Storage]
    end
    
    API --> Email
    API --> FileStorage
```

## 2. User Authentication Flow

```mermaid
sequenceDiagram
    participant U as User
    participant F as Frontend
    participant A as API
    participant DB as Database
    participant E as Email Service
    
    U->>F: Enter credentials
    F->>A: POST /api/auth/login
    A->>DB: Validate credentials
    DB-->>A: User data
    A->>A: Generate JWT token
    A-->>F: Token + User data
    F->>F: Store in Redux + LocalStorage
    F-->>U: Redirect to Dashboard
    
    Note over U,E: Password Reset Flow
    U->>F: Click "Forgot Password"
    F->>A: POST /api/auth/forgot-password
    A->>E: Send reset email
    E-->>U: Reset link
    U->>F: Click reset link
    F->>A: POST /api/auth/reset-password
    A->>DB: Update password
    A-->>F: Success response
```

## 3. Transaction Creation Flow

```mermaid
flowchart TD
    A[User clicks Add Transaction] --> B[Open Transaction Modal]
    B --> C[Fill Transaction Form]
    C --> D{Form Valid?}
    D -->|No| E[Show Validation Errors]
    E --> C
    D -->|Yes| F[Submit to API]
    F --> G[API Validates Data]
    G --> H{Valid?}
    H -->|No| I[Return Error]
    I --> E
    H -->|Yes| J[Save to Database]
    J --> K[Update Cache]
    K --> L[Return Success]
    L --> M[Update Redux Store]
    M --> N[Close Modal]
    N --> O[Refresh Dashboard]
    O --> P[Update Budget Progress]
    P --> Q[Update Reports Data]
    Q --> R[Show Success Notification]
```

## 4. Budget Calculation Flow

```mermaid
graph LR
    A[Transaction Data] --> B[Group by Category]
    B --> C[Sum Amounts by Category]
    C --> D[Get Budget Limits]
    D --> E[Compare Spending vs Budget]
    E --> F{Over Budget?}
    F -->|Yes| G[Generate Alert]
    F -->|No| H[Update Progress Bars]
    G --> I[Send Notification]
    H --> J[Update Dashboard]
    I --> J
    J --> K[Update Budget Page]
    K --> L[Update Reports]
```

## 5. Dashboard Data Aggregation Flow

```mermaid
graph TB
    A[Dashboard Load] --> B[Fetch Multiple Data Sources]
    B --> C[Get Current Month Transactions]
    B --> D[Get Budget Data]
    B --> E[Get Upcoming Bills]
    B --> F[Get Savings Goals]
    B --> G[Get Income Data]
    
    C --> H[Calculate Spending Totals]
    D --> I[Calculate Budget Progress]
    E --> J[Filter Bills Due Soon]
    F --> K[Calculate Savings Progress]
    G --> L[Calculate Income Totals]
    
    H --> M[Aggregate Dashboard Data]
    I --> M
    J --> M
    K --> M
    L --> M
    
    M --> N[Update Redux Store]
    N --> O[Render Dashboard Components]
    O --> P[Display Charts & Stats]
```

## 6. Report Generation Flow

```mermaid
flowchart TD
    A[User Selects Report Type] --> B[Choose Date Range]
    B --> C[Apply Filters]
    C --> D[Query Database]
    D --> E[Aggregate Data]
    E --> F[Generate Charts]
    F --> G[Create Report Object]
    G --> H{Export Format?}
    H -->|PDF| I[Generate PDF]
    H -->|CSV| J[Generate CSV]
    H -->|Display| K[Render Charts]
    I --> L[Download File]
    J --> L
    K --> M[Show in Browser]
```

## 7. Real-time Updates Flow

```mermaid
sequenceDiagram
    participant U1 as User 1
    participant F1 as Frontend 1
    participant WS as WebSocket
    participant S as Server
    participant U2 as User 2
    participant F2 as Frontend 2
    
    U1->>F1: Add Transaction
    F1->>S: API Call
    S->>S: Process & Save
    S->>WS: Broadcast Update
    WS->>F1: Confirm Update
    WS->>F2: Push Update
    F2->>F2: Update UI
    F1->>F1: Update UI
```

## 8. Data Synchronization Flow

```mermaid
graph TB
    A[User Action] --> B[Optimistic Update]
    B --> C[Update Local State]
    C --> D[Update UI]
    D --> E[Send API Request]
    E --> F{Success?}
    F -->|Yes| G[Confirm Update]
    F -->|No| H[Rollback Changes]
    H --> I[Show Error Message]
    G --> J[Update Cache]
    J --> K[Sync with Other Devices]
```

## 9. Bill Payment Flow

```mermaid
flowchart TD
    A[User Marks Bill as Paid] --> B[Create Transaction Record]
    B --> C[Update Bill Status]
    C --> D[Calculate New Balance]
    D --> E[Update Budget Impact]
    E --> F[Send Payment Confirmation]
    F --> G[Update Dashboard]
    G --> H[Refresh Bills Page]
    H --> I[Update Reports]
```

## 10. Savings Goal Progress Flow

```mermaid
graph LR
    A[Savings Transaction] --> B[Identify Goal]
    B --> C[Update Goal Progress]
    C --> D{Goal Complete?}
    D -->|Yes| E[Mark Goal Complete]
    D -->|No| F[Update Progress Bar]
    E --> G[Send Celebration Notification]
    F --> H[Update Dashboard]
    G --> H
    H --> I[Update Savings Page]
    I --> J[Update Reports]
```

## 11. Error Handling Flow

```mermaid
flowchart TD
    A[API Request] --> B{Network Available?}
    B -->|No| C[Store in Queue]
    B -->|Yes| D[Send Request]
    D --> E{Server Response?}
    E -->|Error| F[Handle Error Type]
    E -->|Success| G[Process Response]
    F --> H{Retryable?}
    H -->|Yes| I[Retry Request]
    H -->|No| J[Show Error Message]
    I --> D
    C --> K[Retry When Online]
    K --> D
    G --> L[Update UI]
```

## 12. Data Export Flow

```mermaid
graph TB
    A[User Requests Export] --> B[Select Data Range]
    B --> C[Choose Export Format]
    C --> D[Query All Data]
    D --> E[Format Data]
    E --> F[Generate File]
    F --> G[Store Temporarily]
    G --> H[Create Download Link]
    H --> I[User Downloads]
    I --> J[Cleanup Temp File]
```

## Data Flow Summary

### Key Data Flows:
1. **User Input** → **Validation** → **API** → **Database** → **Cache** → **UI Update**
2. **Real-time Updates** via WebSocket connections
3. **Optimistic Updates** for better user experience
4. **Error Handling** with retry mechanisms
5. **Data Synchronization** across multiple devices
6. **Export/Import** functionality for data portability

### Critical Integration Points:
- **Transactions** are the central data source affecting all other modules
- **Dashboard** aggregates data from all modules for overview
- **Budget** calculations depend on transaction categorization
- **Reports** consume data from all modules for analysis
- **Settings** affect all modules through preferences and categories
