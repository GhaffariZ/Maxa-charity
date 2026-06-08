(function($) {
    'use strict';

    const tagifyBasicEl = document.querySelector('#tagifyBasic');
    const TagifyBasic = new Tagify(tagifyBasicEl);

    
    const tagifyReadonlyEl = document.querySelector('#tagifyReadonly');
    const TagifyReadonly = new Tagify(tagifyReadonlyEl);


  // Custom list & inline suggestion
  //------------------------------------------------------
  const TagifyCustomInlineSuggestionEl = document.querySelector('#TagifyCustomInlineSuggestion');
  const TagifyCustomListSuggestionEl = document.querySelector('#TagifyCustomListSuggestion');

  const whitelist = [
    'Bash',
    'Bourne Shell',
    'C',
    'C++',
    'C#',
    'Clojure',
    'Cobol',
    'Crystal',
    'D',
    'Dart',
    'Delphi/Object Pascal',
    'Elixir',
    'Elm',
    'Erlang',
    'F#',
    'Fortran',
    'Go',
    'Groovy',
    'Haskell',
    'HTML',
    'Java',
    'JavaScript',
    'Julia',
    'Kotlin',
    'Lisp',
    'Lua',
    'MATLAB',
    'Objective-C',
    'Pascal',
    'Perl',
    'PHP',
    'Prolog',
    'Python',
    'R',
    'Ruby',
    'Rust',
    'Scala',
    'Scheme',
    'Shell',
    'SQL',
    'Swift',
    'TypeScript',
    'Visual Basic',
    'Wolfram'
];

  // Inline
  let TagifyCustomInlineSuggestion = new Tagify(TagifyCustomInlineSuggestionEl, {
    whitelist: whitelist,
    maxTags: 10,
    dropdown: {
      maxItems: 20,
      classname: 'tags-inline',
      enabled: 0,
      closeOnSelect: false
    }
  });
  // List
  let TagifyCustomListSuggestion = new Tagify(TagifyCustomListSuggestionEl, {
    whitelist: whitelist,
    maxTags: 10,
    dropdown: {
      maxItems: 20,
      classname: '',
      enabled: 0,
      closeOnSelect: false
    }
  });

  // Users List suggestion
  //------------------------------------------------------
  const TagifyUserListEl = document.querySelector('#TagifyUserList');

  const usersList = [
    {
      value: 1,
      name: 'امیرحسین رضایی',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'jhattersley0@ucsd.edu'
    },
    {
      value: 2,
      name: 'سهراب قاسمی',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'aesson1@ning.com'
    },
    {
      value: 3,
      name: 'آرمین سعیدی',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'abatisse2@nih.gov'
    },
    {
      value: 4,
      name: 'کسری علیزاده',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'gyellowley3@behance.net'
    },
    {
      value: 5,
      name: 'دیبا شریفی',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'dwilford4@jugem.jp'
    },
    {
      value: 6,
      name: 'سپیده افشار',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'corwin5@meetup.com'
    },
    {
      value: 7,
      name: 'سارا کریمی',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'smain6@techcrunch.com'
    },
    {
      value: 8,
      name: 'هستی همتی',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'ghaysman7@mashable.com'
    },
    {
      value: 9,
      name: 'مهران مدیری',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'mmandrake8@sourceforge.net'
    },
    {
      value: 10,
      name: 'کوروش تهرانی',
      avatar: 'assets/img/avatar/11.jpg',
      email: 'ctidey9@youtube.com'
    }
  ];

  function tagTemplate(tagData) {
    return `
    <tag title="${tagData.title || tagData.email}"
      contenteditable='false'
      spellcheck='false'
      tabIndex="-1"
      class="${this.settings.classNames.tag} ${tagData.class ? tagData.class : ''}"
      ${this.getAttributes(tagData)}
    >
      <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>
      <div>
        <div class='tagify__tag__avatar-wrap'>
          <img onerror="this.style.visibility='hidden'" src="${tagData.avatar}">
        </div>
        <span class='tagify__tag-text'>${tagData.name}</span>
      </div>
    </tag>
  `;
  }

  function suggestionItemTemplate(tagData) {
    return `
    <div ${this.getAttributes(tagData)}
      class='tagify__dropdown__item align-items-center ${tagData.class ? tagData.class : ''}'
      tabindex="0"
      role="option"
    >
      ${
        tagData.avatar
          ? `<div class='tagify__dropdown__item__avatar-wrap'>
          <img onerror="this.style.visibility='hidden'" src="${tagData.avatar}">
        </div>`
          : ''
      }
      <div class="fw-medium fs-4">${tagData.name}</div>
      <span class"fs-3">${tagData.email}</span>
    </div>
  `;
  }
  function dropdownHeaderTemplate(suggestions) {
    return `
        <div class="${this.settings.classNames.dropdownItem} ${this.settings.classNames.dropdownItem}__addAll fs-4">
            <strong>${this.value.length ? `اضافه کردن همه` : 'Add All'}</strong>
            <span>${suggestions.length} نفر</span>
        </div>
    `;
  }

  // initialize Tagify on the above input node reference
  let TagifyUserList = new Tagify(TagifyUserListEl, {
    tagTextProp: 'name', // very important since a custom template is used with this property as text. allows typing a "value" or a "name" to match input with whitelist
    enforceWhitelist: true,
    skipInvalid: true, // do not remporarily add invalid tags
    dropdown: {
      closeOnSelect: false,
      enabled: 0,
      classname: 'users-list',
      searchKeys: ['name', 'email'] // very important to set by which keys to search for suggesttions when typing
    },
    templates: {
      tag: tagTemplate,
      dropdownItem: suggestionItemTemplate,
      dropdownHeader: dropdownHeaderTemplate
    },
    whitelist: usersList
  });

  // attach events listeners
  TagifyUserList.on('dropdown:select', onSelectSuggestion) // allows selecting all the suggested (whitelist) items
    .on('edit:start', onEditStart); // show custom text in the tag while in edit-mode

  function onSelectSuggestion(e) {
    // custom class from "dropdownHeaderTemplate"
    if (e.detail.elm.classList.contains(`${TagifyUserList.settings.classNames.dropdownItem}__addAll`))
      TagifyUserList.dropdown.selectAll();
  }

  function onEditStart({ detail: { tag, data } }) {
    TagifyUserList.setTagTextNode(tag, `${data.name} <${data.email}>`);
  }

  }(jQuery) )