class bloomFilter {
    constructor(size) {
      this.size = size;
      this.storage = new Array(size).fill(0, 0);
    }
  
    add(str) {
      this.storage[this.hashIt(str, this.size)] = 1;
      this.storage[this.hashIt2(str, this.size)] = 1;
      this.storage[this.hashIt3(str, this.size)] = 1;
    }
  
    contains(str) {
      return !!this.storage[this.hashIt(str, this.size)] && !!this.storage[this.hashIt2(str, this.size)] && !!this.storage[this.hashIt3(str, this.size)]
    }
  
    hashIt(str, size) {
      let coded = 0;
      for (let i = 0; i < str.length; i++) {
        coded += str[i].charCodeAt() * i + 1;
      }
      return Math.floor(coded % size)
    }
  
    hashIt2(str, size) {
      let coded = 0;
      for (let i = 0; i < str.length; i++) {
        coded += (str[i].charCodeAt() - i) * i + 1;
      }
      return Math.floor((coded * 2) % size)
    }
  
    hashIt3(str, size) {
      let coded = 0;
      for (let i = 0; i < str.length; i++) {
        coded += (str[i].charCodeAt() + i) * i + 1;
      }
      return Math.floor((coded * 3) % size)
    }
  
  }