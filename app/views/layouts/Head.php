<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?? "AMusic" ?></title>
  
  <!-- 1. Thư viện jQuery, Tailwind & DaisyUI (Giữ nguyên của bạn) -->
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />

  <!-- 2. QUAN TRỌNG: Link tải Font chữ và Icon từ Google -->
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
  
  <!-- Font chữ Inter (cho đẹp) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet" />
  
  <!-- Icon Material Symbols (Đây là cái bạn đang thiếu) -->
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

  <!-- 3. Cấu hình Tailwind Theme -->
  <style type="text/tailwindcss">
    @theme {
      --color-primary: #1D2C5E;
      --color-accent: #E57C23;
      --color-background-light: #F5F5F5;
      --color-background-dark: #121212;
      --color-text-light: #333333;
      --color-text-dark: #EAEAEA;
      --font-display: "Inter", sans-serif;
    }
    
    /* 4. QUAN TRỌNG: CSS bắt buộc để icon hiển thị đúng */
    .material-symbols-outlined {
      font-family: 'Material Symbols Outlined';
      font-weight: normal;
      font-style: normal;
      font-size: 24px;
      display: inline-block;
      line-height: 1;
      text-transform: none;
      letter-spacing: normal;
      word-wrap: normal;
      white-space: nowrap;
      direction: ltr;
      /* Hỗ trợ hiển thị tốt hơn trên các trình duyệt */
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      -moz-osx-font-smoothing: grayscale;
      font-feature-settings: 'liga';
    }

    /* Ẩn thanh cuộn nhưng vẫn scroll được */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>

<!-- Code JS useReducer  -->
<script>
  /**
   * @param {Record<string,any>} initState
   * @param {(state:Record<string,any>, action:Record<string,any>)=>Record<string,any>} reducerFunction
   */
  function useReducer(initState, reducerFunction) {
    let state = initState
    const observers = []
    const dispatch = (action) => {
      state = reducerFunction(state, action)
      observers.forEach(fn => fn(state))
    }
    const observer = (voidFn) => {
      observers.push(voidFn)
    }
    return [ dispatch, observer]
  }
</script>