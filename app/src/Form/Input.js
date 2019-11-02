import React, { Component } from 'react';
import './App.css';

class Input extends Component {
    render() {
        return (
            <input className="Input">
                <div className="App-header">
                    <img src={logo} className="App-logo" alt="logo" />
                    <h2>Welcome to React</h2>
                </div>
                <p className="App-intro">
                    To get started, edit <code>src/App.js</code> and save to reload.
                </p>
            </input>
        );
    }
}

export default Input;
